<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use PhpValueObject\Core\Validators\NumericValidator;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * NumericField
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class NumericField extends BaseField
{
    /**
     *
     * @param ?default_factory $defaultFactory
     * @param ?string $alias
     * @param float|int|null $gt より大きい
     * @param float|int|null $lt より小さい
     * @param float|int|null $ge 以上
     * @param float|int|null $le 以下
     */
    public function __construct(
        string|array|Closure|null $defaultFactory = null,
        string|null $alias = null,
        private float|int|null $gt = null,
        private float|int|null $lt = null,
        private float|int|null $ge = null,
        private float|int|null $le = null,
    ) {
        parent::__construct($defaultFactory, $alias);
    }

    /**
     * NumericValidatorを取得
     *
     * @return Validatorable
     */
    public function getValidator(): Validatorable
    {
        return new NumericValidator($this->gt, $this->lt, $this->ge, $this->le);
    }
}
