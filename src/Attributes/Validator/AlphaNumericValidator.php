<?php

declare(strict_types=1);

namespace PhpValueObject\Attributes\Validator;

use PhpValueObject\Validation\Validatable;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class AlphaNumericValidator implements Validatable
{
    public function validate(mixed $value): bool
    {
        $stringValue = (string) $value;

        // 空文字の場合はチェックしない
        if ($stringValue === '') {
            return true;
        }

        return preg_match('/^[a-zA-Z0-9]+$/', (string) $value) === 1;
    }

    public function errorMessage(): string
    {
        return 'property must be alphanumeric.';
    }
}
