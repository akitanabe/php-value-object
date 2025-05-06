<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use PhpValueObject\Core\Definitions\StringValidatorDefinition;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * 文字列のバリデーターを実装するクラス
 */
class StringValidator implements Validatorable
{
    use ValidatorBuildTrait;

    /**
     * @param StringValidatorDefinition $definition バリデーション定義
     */
    public function __construct(
        private StringValidatorDefinition $definition,
    ) {}

    /**
     * 文字列のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @param ValidatorFunctionWrapHandler|null $handler バリデーションハンドラー
     * @return string バリデーション後の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $invalidMessage = 'Invalid Field Value';
        if (is_string($value) === false) {
            throw new ValidationException("{$invalidMessage}. Must be string");
        }

        if ($this->definition->allowEmpty === false && $value === '') {
            throw new ValidationException("{$invalidMessage}. Field Value cannot be empty");
        }

        if ($value === '') {
            return $value;
        }

        $valueLength = mb_strlen($value);

        if ($valueLength < $this->definition->minLength) {
            throw new ValidationException(
                "{$invalidMessage}. Too short. Must be at least {$this->definition->minLength} characters",
            );
        }

        if ($valueLength > $this->definition->maxLength) {
            throw new ValidationException(
                "{$invalidMessage}. Too long. Must be at most {$this->definition->maxLength} characters",
            );
        }

        if ($this->definition->pattern !== '' && preg_match($this->definition->pattern, $value) === 0) {
            throw new ValidationException("{$invalidMessage}. Invalid format");
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
