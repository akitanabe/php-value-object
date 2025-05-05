<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Definitions;

/**
 * 数値バリデーションの定義を保持するクラス
 */
class NumericValidatorDefinition
{
    /**
     * @param float|int|null $gt より大きい
     * @param float|int|null $lt より小さい
     * @param float|int|null $ge 以上
     * @param float|int|null $le 以下
     */
    public function __construct(
        public readonly float|int|null $gt = null,
        public readonly float|int|null $lt = null,
        public readonly float|int|null $ge = null,
        public readonly float|int|null $le = null,
    ) {}
}
