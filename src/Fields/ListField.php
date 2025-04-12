<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use Override;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Support\ValidatorFunctionWrapHandler;

/**
 * ListField
 * @phpstan-template T
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ListField extends BaseField
{
    private readonly ?PropertyValueType $valueType;

    /**
     * @param ?string $type リストの要素の型名（"int", "float", "string", "object"など）またはクラス名
     * @param ?default_factory $defaultFactory
     * @param ?string $alias
     */
    public function __construct(
        private readonly ?string $type = null,
        string|array|Closure|null $defaultFactory = null,
        ?string $alias = null,
    ) {
        parent::__construct($defaultFactory, $alias);

        if ($type === null) {
            $this->valueType = null;
            return;
        }

        $this->valueType = (class_exists($type)
            ? PropertyValueType::OBJECT
            : PropertyValueType::fromShorthand($type));
    }

    /**
     * 配列のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    #[Override]
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        if (!is_array($value)) {
            throw new ValidationException("Invalid Field Value. Must be array");
        }

        if (!array_is_list($value)) {
            throw new ValidationException("Invalid Field Value. Must be list");
        }

        // 型の指定がない場合は配列とリストの検証のみ
        if ($this->type === null || $this->valueType === null) {
            return $value;
        }

        $listValidation = match (true) {
            // クラスが指定されている場合
            ($this->valueType === PropertyValueType::OBJECT && class_exists($this->type))
            => fn(mixed $element): bool => is_object($element) && $element instanceof $this->type,

            // プリミティブ型 or $typeがobjectの場合
            default => fn(mixed $element): bool => gettype($element) === $this->valueType->value,
        };

        $isValid = array_all($value, $listValidation);

        if (!$isValid) {
            throw new ValidationException("Invalid element type");
        }

        return $value;
    }
}
