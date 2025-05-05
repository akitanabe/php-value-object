<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use PhpValueObject\Core\Definitions\NumericValidatorDefinition;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * 数値のバリデーターを実装するクラス
 */
class NumericValidator implements Validatorable
{
    /**
     * @param NumericValidatorDefinition $definition バリデーション定義
     */
    public function __construct(
        private NumericValidatorDefinition $definition,
    ) {}

    /**
     * 数値のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @param ValidatorFunctionWrapHandler|null $handler バリデーションハンドラー
     * @return mixed バリデーション後の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $invalidMessage = 'Invalid Field Value';
        if (!is_numeric($value)) {
            throw new ValidationException("{$invalidMessage}. Must be numeric");
        }

        $numericValue = (float) $value;

        // gt (>) の検証
        if ($this->definition->gt !== null && $numericValue <= $this->definition->gt) {
            throw new ValidationException("{$invalidMessage}. Must be greater than {$this->definition->gt}");
        }

        // lt (<) の検証
        if ($this->definition->lt !== null && $numericValue >= $this->definition->lt) {
            throw new ValidationException("{$invalidMessage}. Must be less than {$this->definition->lt}");
        }

        // ge (>=) の検証
        if ($this->definition->ge !== null && $numericValue < $this->definition->ge) {
            throw new ValidationException(
                "{$invalidMessage}. Must be greater than or equal to {$this->definition->ge}",
            );
        }

        // le (<=) の検証
        if ($this->definition->le !== null && $numericValue > $this->definition->le) {
            throw new ValidationException(
                "{$invalidMessage}. Must be less than or equal to {$this->definition->le}",
            );
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
