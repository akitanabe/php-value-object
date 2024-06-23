<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Validation;

interface Validatable
{
    public function validate(mixed $value): bool;
    public function errorMessage(): string;
}
