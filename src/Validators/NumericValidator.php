<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Enums\ValidatorMode;

/**
 * 数値のバリデーターを実装するクラス
 */
class NumericValidator implements Validatorable
{
    /**
     * @param float|int|null $gt より大きい
     * @param float|int|null $lt より小さい
     * @param float|int|null $ge 以上
     * @param float|int|null $le 以下
     */
    public function __construct(
        private float|int|null $gt = null,
        private float|int|null $lt = null,
        private float|int|null $ge = null,
        private float|int|null $le = null,
    ) {
    }

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
        if ($this->gt !== null && $numericValue <= $this->gt) {
            throw new ValidationException("{$invalidMessage}. Must be greater than {$this->gt}");
        }

        // lt (<) の検証
        if ($this->lt !== null && $numericValue >= $this->lt) {
            throw new ValidationException("{$invalidMessage}. Must be less than {$this->lt}");
        }

        // ge (>=) の検証
        if ($this->ge !== null && $numericValue < $this->ge) {
            throw new ValidationException("{$invalidMessage}. Must be greater than or equal to {$this->ge}");
        }

        // le (<=) の検証
        if ($this->le !== null && $numericValue > $this->le) {
            throw new ValidationException("{$invalidMessage}. Must be less than or equal to {$this->le}");
        }

        $validatedValue = $value;

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
