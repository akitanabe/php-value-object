<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validation;

use PhpValueObject\Exceptions\ValidationException;

class TestValidator
{
    public static function validateLength(string $value): string
    {
        if (strlen($value) < 3) {
            throw new ValidationException('3文字以上必要です');
        }
        return $value;
    }

    public static function formatName(string $value): string
    {
        return ucfirst($value);
    }
}
