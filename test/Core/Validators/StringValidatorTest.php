<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PHPUnit\Framework\TestCase;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Core\Validators\StringValidator;

class StringValidatorTest extends TestCase
{
    public function testValidateReturnsStringWhenValid(): void
    {
        $validator = new StringValidator();
        $result = $validator->validate('test string');

        $this->assertSame('test string', $result);
    }

    public function testValidateThrowsExceptionWhenValueIsNotString(): void
    {
        $validator = new StringValidator();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be string');

        $validator->validate(123);
    }

    public function testValidateThrowsExceptionWhenEmptyStringNotAllowed(): void
    {
        $validator = new StringValidator(allowEmpty: false);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Field Value cannot be empty');

        $validator->validate('');
    }

    public function testValidateAllowsEmptyStringWhenAllowed(): void
    {
        $validator = new StringValidator(allowEmpty: true);
        $result = $validator->validate('');

        $this->assertSame('', $result);
    }

    public function testValidateThrowsExceptionWhenStringIsTooShort(): void
    {
        $validator = new StringValidator(minLength: 5);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Too short. Must be at least 5 characters');

        $validator->validate('abc');
    }

    public function testValidateThrowsExceptionWhenStringIsTooLong(): void
    {
        $validator = new StringValidator(maxLength: 5);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Too long. Must be at most 5 characters');

        $validator->validate('abcdefg');
    }

    public function testValidateThrowsExceptionWhenPatternDoesNotMatch(): void
    {
        $validator = new StringValidator(pattern: '/^[a-z]+$/');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Invalid format');

        $validator->validate('123');
    }

    public function testValidateAcceptsStringMatchingPattern(): void
    {
        $validator = new StringValidator(pattern: '/^[a-z]+$/');
        $result = $validator->validate('abc');

        $this->assertSame('abc', $result);
    }

}
