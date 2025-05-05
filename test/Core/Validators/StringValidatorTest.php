<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PHPUnit\Framework\TestCase;
use PhpValueObject\Core\Definitions\StringValidatorDefinition;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Core\Validators\StringValidator;

class StringValidatorTest extends TestCase
{
    public function testValidateReturnsStringWhenValid(): void
    {
        $definition = new StringValidatorDefinition();
        $validator = new StringValidator($definition);
        $result = $validator->validate('test string');

        $this->assertSame('test string', $result);
    }

    public function testValidateThrowsExceptionWhenValueIsNotString(): void
    {
        $definition = new StringValidatorDefinition();
        $validator = new StringValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be string');

        $validator->validate(123);
    }

    public function testValidateThrowsExceptionWhenEmptyStringNotAllowed(): void
    {
        $definition = new StringValidatorDefinition(allowEmpty: false);
        $validator = new StringValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Field Value cannot be empty');

        $validator->validate('');
    }

    public function testValidateAllowsEmptyStringWhenAllowed(): void
    {
        $definition = new StringValidatorDefinition(allowEmpty: true);
        $validator = new StringValidator($definition);
        $result = $validator->validate('');

        $this->assertSame('', $result);
    }

    public function testValidateThrowsExceptionWhenStringIsTooShort(): void
    {
        $definition = new StringValidatorDefinition(minLength: 5);
        $validator = new StringValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Too short. Must be at least 5 characters');

        $validator->validate('abc');
    }

    public function testValidateThrowsExceptionWhenStringIsTooLong(): void
    {
        $definition = new StringValidatorDefinition(maxLength: 5);
        $validator = new StringValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Too long. Must be at most 5 characters');

        $validator->validate('abcdefg');
    }

    public function testValidateThrowsExceptionWhenPatternDoesNotMatch(): void
    {
        $definition = new StringValidatorDefinition(pattern: '/^[a-z]+$/');
        $validator = new StringValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Invalid format');

        $validator->validate('123');
    }

    public function testValidateAcceptsStringMatchingPattern(): void
    {
        $definition = new StringValidatorDefinition(pattern: '/^[a-z]+$/');
        $validator = new StringValidator($definition);
        $result = $validator->validate('abc');

        $this->assertSame('abc', $result);
    }
}
