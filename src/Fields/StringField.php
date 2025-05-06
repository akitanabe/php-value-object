<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use PhpValueObject\Core\Definitions\StringValidatorDefinition;
use PhpValueObject\Core\Validators\StringValidator;

/**
 * StringField
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class StringField extends BaseField
{
    /**
     * バリデーション定義
     */
    private StringValidatorDefinition $definition;

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
        bool $allowEmpty = true,
        int $minLength = 1,
        int $maxLength = PHP_INT_MAX,
        string $pattern = '',
    ) {
        parent::__construct($defaultFactory, $alias);
        $this->definition = new StringValidatorDefinition($allowEmpty, $minLength, $maxLength, $pattern);
    }

    public function getValidator(): string
    {
        return StringValidator::class;
    }

    public function getDefinition(): object
    {
        return $this->definition;
    }
}
