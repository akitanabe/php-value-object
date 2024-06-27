<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\Validator\EmptyStringValidator;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;

final class AllowEmptyStringValue extends BaseValueObject
{
    public string $string = '';
}

final class NotAllowEmptyStringValue extends BaseValueObject
{
    #[EmptyStringValidator]
    public string $string = '';
}

class EmptyStringValidatorTest extends TestCase
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
        $this->expectException(ValidationException::class);
        new NotAllowEmptyStringValue();
    }
}
