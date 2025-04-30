<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use PhpValueObject\Core\Validators\StringValidator;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * StringField
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class StringField extends BaseField
{
    /**
     *
     * @param ?default_factory $defaultFactory
     * @param ?string $alias
     * @param bool $allowEmpty
     * @param positive-int $minLength
     * @param positive-int $maxLength
     * @param string $pattern
     */
    public function __construct(
        string|array|Closure|null $defaultFactory = null,
        string|null $alias = null,
        private bool $allowEmpty = true,
        private int $minLength = 1,
        private int $maxLength = PHP_INT_MAX,
        private string $pattern = '',
    ) {
        parent::__construct($defaultFactory, $alias);
    }

    /**
     * StringValidatorを取得
     *
     * @return Validatorable
     */
    public function getValidator(): Validatorable
    {
        return new StringValidator($this->allowEmpty, $this->minLength, $this->maxLength, $this->pattern);
    }
}
