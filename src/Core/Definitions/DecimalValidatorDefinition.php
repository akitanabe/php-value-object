<?php

declare(strict_types=1);

namespace PhSculptis\Core\Definitions;

/**
 * 小数値バリデーションの定義を保持するクラス
 */
class DecimalValidatorDefinition extends NumericValidatorDefinition
{
    /**
     * @param ?non-negative-int $maxDigits 合計桁数の制限
     * @param ?non-negative-int $decimalPlaces 小数点以下の桁数制限
     * @param float|int|null $gt より大きい
     * @param float|int|null $lt より小さい
     * @param float|int|null $ge 以上
     * @param float|int|null $le 以下
     */
    public function __construct(
        public readonly ?int $maxDigits = null,
        public readonly ?int $decimalPlaces = null,
        float|int|null $gt = null,
        float|int|null $lt = null,
        float|int|null $ge = null,
        float|int|null $le = null,
    ) {
        parent::__construct($gt, $lt, $ge, $le);
    }
}
