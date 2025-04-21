<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Enums\ValidatorMode;

/**
 * 小数値のバリデーターを実装するクラス
 */
class DecimalValidator implements Validatorable
{
    /**
     * 数値範囲のバリデーションを行うためのNumericValidatorインスタンス
     */
    private NumericValidator $numericValidator;

    /**
     * @param ?non-negative-int $maxDigits 合計桁数の制限
     * @param ?non-negative-int $decimalPlaces 小数点以下の桁数制限
     * @param float|int|null $gt より大きい
     * @param float|int|null $lt より小さい
     * @param float|int|null $ge 以上
     * @param float|int|null $le 以下
     */
    public function __construct(
        private ?int $maxDigits = null,
        private ?int $decimalPlaces = null,
        float|int|null $gt = null,
        float|int|null $lt = null,
        float|int|null $ge = null,
        float|int|null $le = null,
    ) {
        $this->numericValidator = new NumericValidator($gt, $lt, $ge, $le);
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
        if ($this->maxDigits !== null) {
            $digits = strlen(str_replace(['.', '-'], '', $stringValue));
            if ($digits > $this->maxDigits) {
                throw new ValidationException(
                    "Invalid Field Value. Number must have no more than {$this->maxDigits} digits in total",
                );
            }
        }

        // Then decimal places validation
        if ($this->decimalPlaces !== null) {
            $parts = explode('.', $stringValue);
            $currentPlaces = strlen($parts[1] ?? '');
            if ($currentPlaces > $this->decimalPlaces) {
                throw new ValidationException(
                    "Invalid Field Value. Number must have no more than {$this->decimalPlaces} decimal places",
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

    /**
     * バリデーション処理の実行順序を取得する
     *
     * @return ValidatorMode
     */
    public function getMode(): ValidatorMode
    {
        return ValidatorMode::INTERNAL;
    }
}
