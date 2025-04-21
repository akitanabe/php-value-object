<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PHPUnit\Framework\TestCase;
use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validators\NumericValidator;

class NumericValidatorTest extends TestCase
{
    public function testValidateReturnsNumericWhenValid(): void
    {
        $validator = new NumericValidator();
        $result = $validator->validate(123);

        $this->assertSame(123, $result);
    }

    public function testValidateThrowsExceptionWhenValueIsNotNumeric(): void
    {
        $validator = new NumericValidator();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be numeric');

        $validator->validate('not a number');
    }

    public function testValidateThrowsExceptionWhenValueIsNotGreaterThan(): void
    {
        $validator = new NumericValidator(gt: 10);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be greater than 10');

        $validator->validate(5);
    }

    public function testValidateThrowsExceptionWhenValueIsNotLessThan(): void
    {
        $validator = new NumericValidator(lt: 10);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be less than 10');

        $validator->validate(15);
    }

    public function testValidateThrowsExceptionWhenValueIsNotGreaterThanOrEqual(): void
    {
        $validator = new NumericValidator(ge: 10);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be greater than or equal to 10');

        $validator->validate(9);
    }

    public function testValidateThrowsExceptionWhenValueIsNotLessThanOrEqual(): void
    {
        $validator = new NumericValidator(le: 10);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be less than or equal to 10');

        $validator->validate(11);
    }

    public function testValidateAcceptsNumericStringValue(): void
    {
        $validator = new NumericValidator();
        $result = $validator->validate('123');

        $this->assertSame('123', $result);
    }

    public function testGetModeReturnsInternal(): void
    {
        $validator = new NumericValidator();

        $this->assertSame(ValidatorMode::INTERNAL, $validator->getMode());
    }
}
