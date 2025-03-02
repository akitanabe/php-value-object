<?php

declare(strict_types=1);

use PhpValueObject\Attributes\Validator\AlphaNumericValidator;
use PhpValueObject\BaseModel;
use PhpValueObject\Exceptions\ValidationException;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmptyStringValue extends BaseModel
{
    #[AlphaNumericValidator]
    public string $string = '';
}

final class AlphaNumericValue extends BaseModel
{
    #[AlphaNumericValidator]
    public string $alphanumeric = 'abc123';

    #[AlphaNumericValidator]
    public bool $int = true;

    #[AlphaNumericValidator]
    public string $alphabet = 'abc';
}

final class ExceptionAlphaNumericValue extends BaseModel
{
    #[AlphaNumericValidator]
    public string $alphanumeric = '_abc123';

    #[AlphaNumericValidator]
    public float $float = 1.23;
}

class AlphaNumericValidatorTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function isAlphaNumericValue(): void
    {
        AlphaNumericValue::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function isEmptyString(): void
    {
        EmptyStringValue::fromArray();
    }

    #[Test]
    public function isExceptionAlphanumericValue(): void
    {
        $this->expectException(ValidationException::class);
        ExceptionAlphaNumericValue::fromArray();
    }
}
