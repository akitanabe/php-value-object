<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use PhpValueObject\Core\Definitions\ListValidatorDefinition;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * リスト（配列）のバリデーターを実装するクラス
 * @phpstan-template T
 */
class ListValidator implements Validatorable
{
    private readonly ?PropertyValueType $valueType;

    /**
     * @param ListValidatorDefinition $definition バリデーション定義
     */
    public function __construct(
        private readonly ListValidatorDefinition $definition,
    ) {
        if ($this->definition->type === null) {
            $this->valueType = null;
            return;
        }

        $this->valueType = (class_exists($this->definition->type)
            ? PropertyValueType::OBJECT
            : PropertyValueType::fromShorthand($this->definition->type));
    }

    /**
     * 配列のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @param ValidatorFunctionWrapHandler|null $handler バリデーションハンドラー
     * @return mixed バリデーション後の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        if (!is_array($value)) {
            throw new ValidationException("Invalid Field Value. Must be array");
        }

        if (!array_is_list($value)) {
            throw new ValidationException("Invalid Field Value. Must be list");
        }

        // 型の指定がない場合は配列とリストの検証のみ
        if ($this->definition->type === null || $this->valueType === null) {
            return $value;
        }

        $listValidation = match (true) {
            // クラスが指定されている場合
            ($this->valueType === PropertyValueType::OBJECT && class_exists($this->definition->type))
            => fn(mixed $element): bool => is_object($element) && $element instanceof $this->definition->type,

            // プリミティブ型 or $typeがobjectの場合
            default => fn(mixed $element): bool => gettype($element) === $this->valueType->value,
        };

        $isValid = array_all($value, $listValidation);

        if (!$isValid) {
            throw new ValidationException("Invalid element type");
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
