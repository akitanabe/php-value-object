<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\Validator\AlphaNumericValidator;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;

final class EmptyStringValue extends BaseValueObject
{
    #[AlphaNumericValidator]
    public string $string = '';
}

final class AlphaNumericValue extends BaseValueObject
{
    #[AlphaNumericValidator]
    public string $alphanumeric = 'abc123';

    #[AlphaNumericValidator]
    public bool $int = true;

    #[AlphaNumericValidator]
    public string $alphabet = 'abc';
}

final class ExceptionAlphaNumericValue extends BaseValueObject
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
    public function isAlphaNumericValue()
    {
        new AlphaNumericValue();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function isEmptyString()
    {
        new EmptyStringValue();
    }

    #[Test]
    public function isExceptionAlphanumericValue()
    {
        $this->expectException(ValidationException::class);
        new ExceptionAlphaNumericValue();
    }
}
