<?php

declare(strict_types=1);

namespace PhSculptis\Core\Validators;

use PhSculptis\Core\Definitions\DecimalValidatorDefinition;
use PhSculptis\Exceptions\ValidationException;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;

/**
 * 小数値のバリデーターを実装するクラス
 */
class DecimalValidator implements Validatorable
{
    use ValidatorBuildTrait;

    /**
     * 数値範囲のバリデーションを行うためのNumericValidatorインスタンス
     */
    private NumericValidator $numericValidator;

    /**
     * @param DecimalValidatorDefinition $definition バリデーション定義
     */
    public function __construct(
        private DecimalValidatorDefinition $definition,
    ) {
        $this->numericValidator = new NumericValidator($definition);
    }

    /**
     * 小数値のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @param ValidatorFunctionWrapHandler|null $handler バリデーションハンドラー
     * @return mixed バリデーション後の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        if (!is_numeric($value)) {
            throw new ValidationException('Invalid Field Value. Must be numeric');
        }

        $stringValue = (string) $value;

        // Validate max digits first
        if ($this->definition->maxDigits !== null) {
            $digits = strlen(str_replace(['.', '-'], '', $stringValue));
            if ($digits > $this->definition->maxDigits) {
                throw new ValidationException(
                    "Invalid Field Value. Number must have no more than {$this->definition->maxDigits} digits in total",
                );
            }
        }

        // Then decimal places validation
        if ($this->definition->decimalPlaces !== null) {
            $parts = explode('.', $stringValue);
            $currentPlaces = strlen($parts[1] ?? '');
            if ($currentPlaces > $this->definition->decimalPlaces) {
                throw new ValidationException(
                    "Invalid Field Value. Number must have no more than {$this->definition->decimalPlaces} decimal places",
                );
            }
        }

        // After our validations, pass to NumericValidator for gt, lt, ge, le checks
        $validatedValue = $this->numericValidator->validate($value);

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
