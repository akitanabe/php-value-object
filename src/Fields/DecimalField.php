<?php

declare(strict_types=1);

namespace PhSculptis\Fields;

use Attribute;
use Closure;
use PhSculptis\Core\Definitions\DecimalValidatorDefinition;
use PhSculptis\Core\Validators\DecimalValidator;

/**
 * DecimalField
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DecimalField extends BaseField
{
    /**
     * バリデーション定義
     */
    private DecimalValidatorDefinition $definition;

    /**
     *
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
        string|null $alias = null,
        ?int $maxDigits = null,
        ?int $decimalPlaces = null,
        float|int|null $gt = null,
        float|int|null $lt = null,
        float|int|null $ge = null,
        float|int|null $le = null,
    ) {
        parent::__construct($defaultFactory, $alias);
        $this->definition = new DecimalValidatorDefinition($maxDigits, $decimalPlaces, $gt, $lt, $ge, $le);
    }

    public function getValidator(): string
    {
        return DecimalValidator::class;
    }

    public function getDefinition(): object
    {
        return $this->definition;
    }
}
