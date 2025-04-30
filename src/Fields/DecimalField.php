<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use PhpValueObject\Core\Validators\DecimalValidator;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * 小数値を扱うフィールドクラス
 *
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DecimalField extends BaseField
{
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
        private float|int|null $gt = null,
        private float|int|null $lt = null,
        private float|int|null $ge = null,
        private float|int|null $le = null,
    ) {
        parent::__construct($defaultFactory, $alias);
    }

    /**
     * DecimalValidatorを取得
     *
     * @return Validatorable
     */
    public function getValidator(): Validatorable
    {
        return new DecimalValidator(
            $this->maxDigits,
            $this->decimalPlaces,
            $this->gt,
            $this->lt,
            $this->ge,
            $this->le,
        );
    }
}
