<?php

declare(strict_types=1);

namespace PhpValueObject\Attributes\Validator;

use PhpValueObject\Validation\Validatable;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class NotEmptyStringValidator implements Validatable
{
    public function validate(mixed $value): bool
    {
        return (string) $value !== '';
    }

    public function errorMessage(): string
    {
        return 'property not allowed empty string.';
    }
}
