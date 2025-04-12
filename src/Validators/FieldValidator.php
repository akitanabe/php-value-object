<?php

namespace PhpValueObject\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use Attribute;
use PhpValueObject\Support\ValidatorFunctionWrapHandler;

/**
 * @phpstan-import-type validator_mode from Validatorable
 * @phpstan-import-type validator_callable from Validatorable
 *
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class FieldValidator implements Validatorable
{
    private Closure $validator;

    /**
     * @param validator_mode $mode
     */
    public function __construct(
        public readonly string $field,
        private string $mode = 'after',
    ) {}

    public function setValidator(Closure $validator): void
    {
        $this->validator = $validator;
    }

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $validatorFn = FieldsHelper::createFactory($this->validator);

        return $validatorFn($value);
    }

    /**
     * @return validator_mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }
}
