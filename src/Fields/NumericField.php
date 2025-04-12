<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use Override;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Support\ValidatorFunctionWrapHandler;

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
     * 数値のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    #[Override]
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

        return $value;
    }
}
