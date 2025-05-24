<?php

declare(strict_types=1);

namespace PhSculptis\Test\Core\Validators;

use PHPUnit\Framework\TestCase;
use PhSculptis\Core\Definitions\NumericValidatorDefinition;
use PhSculptis\Core\Validators\NumericValidator;
use PhSculptis\Exceptions\ValidationException;

class NumericValidatorTest extends TestCase
{
    public function testValidateReturnsNumericWhenValid(): void
    {
        $definition = new NumericValidatorDefinition();
        $validator = new NumericValidator($definition);
        $result = $validator->validate(123);

        $this->assertSame(123, $result);
    }

    public function testValidateThrowsExceptionWhenValueIsNotNumeric(): void
    {
        $definition = new NumericValidatorDefinition();
        $validator = new NumericValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be numeric');

        $validator->validate('not a number');
    }

    public function testValidateThrowsExceptionWhenValueIsNotGreaterThan(): void
    {
        $definition = new NumericValidatorDefinition(gt: 10);
        $validator = new NumericValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be greater than 10');

        $validator->validate(5);
    }

    public function testValidateThrowsExceptionWhenValueIsNotLessThan(): void
    {
        $definition = new NumericValidatorDefinition(lt: 10);
        $validator = new NumericValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be less than 10');

        $validator->validate(15);
    }

    public function testValidateThrowsExceptionWhenValueIsNotGreaterThanOrEqual(): void
    {
        $definition = new NumericValidatorDefinition(ge: 10);
        $validator = new NumericValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be greater than or equal to 10');

        $validator->validate(9);
    }

    public function testValidateThrowsExceptionWhenValueIsNotLessThanOrEqual(): void
    {
        $definition = new NumericValidatorDefinition(le: 10);
        $validator = new NumericValidator($definition);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be less than or equal to 10');

        $validator->validate(11);
    }

    public function testValidateAcceptsNumericStringValue(): void
    {
        $definition = new NumericValidatorDefinition();
        $validator = new NumericValidator($definition);
        $result = $validator->validate('123');

        $this->assertSame('123', $result);
    }
}
