<?php

declare(strict_types=1);

use PhpValueObject\Attributes\Validator\NotEmptyStringValidator;
use PhpValueObject\BaseModel;
use PhpValueObject\Exceptions\ValidationException;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AllowEmptyStringValue extends BaseModel
{
    public string $string = '';
}

final class NotAllowEmptyStringValue extends BaseModel
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
        AllowEmptyStringValue::fromArray();
    }

    #[Test]
    public function notAllowEmptyString(): void
    {
        $this->expectException(ValidationException::class);
        NotAllowEmptyStringValue::fromArray();
    }
}
