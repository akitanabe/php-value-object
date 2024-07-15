<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\Validator\NotEmptyStringValidator;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;

final class AllowEmptyStringValue extends BaseValueObject
{
    public string $string = '';
}

final class NotAllowEmptyStringValue extends BaseValueObject
{
    #[NotEmptyStringValidator]
    public string $string = '';
}

class NotEmptyStringValidatorTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowEmptyString(): void
    {
        new AllowEmptyStringValue();
    }

    #[Test]
    public function notAllowEmptyString(): void
    {
        $this->expectException(ValidationException::class);
        new NotAllowEmptyStringValue();
    }
}
