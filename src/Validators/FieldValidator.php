<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use Attribute;
use PhpValueObject\Enums\ValidatorMode;

/**
 * @phpstan-import-type validator_callable from Validatorable
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class FieldValidator implements Validatorable
{
    private Closure $validator;

    /**
     * @param ValidatorMode $mode
     */
    public function __construct(
        public readonly string $field,
        private ValidatorMode $mode = ValidatorMode::AFTER,
    ) {}

    public function setValidator(Closure $validator): void
    {
        $this->validator = $validator;
    }

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $validator = FieldsHelper::createFactory($this->validator);
        $args = ($handler !== null) ? [$value, $handler] : [$value];

        return $validator(...$args);
    }

    /**
     * @return ValidatorMode
     */
    public function getMode(): ValidatorMode
    {
        return $this->mode;
    }
}
