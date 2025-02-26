<?php

declare(strict_types=1);

namespace PhpValueObject\Validation;

interface Validatable
{
    public function validate(mixed $value): bool;

    public function errorMessage(): string;
}
