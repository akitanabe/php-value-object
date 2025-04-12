<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use Override;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Support\ValidatorFunctionWrapHandler;

/**
 * 小数値を扱うフィールドクラス
 *
 * 以下の機能を提供:
 * - 数値型のバリデーション（is_numericによる検証）
 * - 合計桁数の制限（整数部と小数部の合計）
 * - 小数点以下の桁数制限
 * - 数値の範囲チェック（gt: より大きい, lt: より小さい, ge: 以上, le: 以下）
 *
 * 検証順序:
 * 1. 数値形式の検証
 * 2. 合計桁数の検証
 * 3. 小数点以下の桁数検証
 * 4. 数値範囲の検証（NumericFieldに委譲）
 *
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DecimalField extends BaseField
{
    /**
     * 数値範囲のバリデーションを行うためのNumericFieldインスタンス
     * gt（より大きい）、lt（より小さい）、ge（以上）、le（以下）の検証を委譲
     */
    private NumericField $numericField;

    /**
     * @param ?default_factory $defaultFactory
     * @param ?string $alias
     * @param ?non-negative-int $maxDigits 合計桁数の制限
     * @param ?non-negative-int $decimalPlaces 小数点以下の桁数制限
     * @param float|int|null $gt より大きい
     * @param float|int|null $lt より小さい
     * @param float|int|null $ge 以上
     * @param float|int|null $le 以下
     */
    public function __construct(
        string|array|Closure|null $defaultFactory = null,
        ?string $alias = null,
        private ?int $maxDigits = null,
        private ?int $decimalPlaces = null,
        float|int|null $gt = null,
        float|int|null $lt = null,
        float|int|null $ge = null,
        float|int|null $le = null,
    ) {
        parent::__construct($defaultFactory, $alias);
        $this->numericField = new NumericField(gt: $gt, lt: $lt, ge: $ge, le: $le,);
    }

    /**
     * 小数値のバリデーションを実行
     *
     * バリデーションの流れ:
     * 1. 数値形式の検証（is_numeric）
     * 2. 合計桁数の検証（maxDigits）
     *    - 数値を文字列に変換し、小数点と負号を除いた文字数をカウント
     * 3. 小数点以下の桁数検証（decimalPlaces）
     *    - 小数点で分割し、小数部の長さをチェック
     * 4. 数値範囲の検証（NumericFieldに委譲）
     *
     * @param mixed $value バリデーション対象の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    #[Override]
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

        // After our validations, pass to NumericField for gt, lt, ge, le checks
        $this->numericField->validate($value);

        return $value;
    }
}
