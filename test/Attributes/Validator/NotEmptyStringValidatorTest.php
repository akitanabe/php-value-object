<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\Validator\NotEmptyStringValidator;
use Akitanabe\PhpValueObject\Exceptions\PhpValueObjectValidationException;

class AllowEmptyStringValue extends BaseValueObject
{
    public string $string = '';
}

class NotAllowEmptyStringValue extends BaseValueObject
{
    #[NotEmptyStringValidator]
    public string $string = '';
}

class NotEmptyStringValidatorTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowEmptyString()
    {
        new AllowEmptyStringValue();
    }

    #[Test]
    public function notAllowEmptyString()
    {
        $this->expectException(PhpValueObjectValidationException::class);
        new NotAllowEmptyStringValue();
    }
}
