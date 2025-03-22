<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\NumericField;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use ReflectionProperty;

class NumericFieldValidateTestClass
{
    public float $prop;
}

class NumericFieldTest extends TestCase
{
    /**
     * @return array<string, array{
     *   value: mixed,
     *   expectException: bool,
     *   gt?: float|int,
     *   lt?: float|int,
     *   ge?: float|int,
     *   le?: float|int
     * }>
     */
    public static function validateDataProvider(): array
    {
        return [
            '整数の場合は検証が成功する' => [
                'value' => 123,
                'expectException' => false,
            ],
            '小数の場合は検証が成功する' => [
                'value' => 123.45,
                'expectException' => false,
            ],
            '文字列として表現された数値の場合は検証が成功する' => [
                'value' => "123.45",
                'expectException' => false,
            ],
            '数値以外の場合は例外が発生する' => [
                'value' => 'abc',
                'expectException' => true,
            ],
            'gtで指定した値より大きい場合は検証が成功する' => [
                'value' => 15,
                'expectException' => false,
                'gt' => 10,
            ],
            'gtで指定した値以下の場合は例外が発生する' => [
                'value' => 5,
                'expectException' => true,
                'gt' => 10,
            ],
            'ltで指定した値より小さい場合は検証が成功する' => [
                'value' => 15,
                'expectException' => false,
                'lt' => 20,
            ],
            'ltで指定した値以上の場合は例外が発生する' => [
                'value' => 25,
                'expectException' => true,
                'lt' => 20,
            ],
            'geで指定した値以上の場合は検証が成功する' => [
                'value' => 20,
                'expectException' => false,
                'ge' => 20,
            ],
            'geで指定した値より小さい場合は例外が発生する' => [
                'value' => 15,
                'expectException' => true,
                'ge' => 20,
            ],
            'leで指定した値以下の場合は検証が成功する' => [
                'value' => 20,
                'expectException' => false,
                'le' => 20,
            ],
            'leで指定した値より大きい場合は例外が発生する' => [
                'value' => 25,
                'expectException' => true,
                'le' => 20,
            ],
            'ゼロ値の場合は検証が成功する' => [
                'value' => 0,
                'expectException' => false,
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $expectException
     * @param float|int|null $gt
     * @param float|int|null $lt
     * @param float|int|null $ge
     * @param float|int|null $le
     */
    #[Test]
    #[DataProvider('validateDataProvider')]
    public function testValidate(
        mixed $value,
        bool $expectException,
        float|int|null $gt = null,
        float|int|null $lt = null,
        float|int|null $ge = null,
        float|int|null $le = null,
    ): void {
        $field = new NumericField(gt: $gt, lt: $lt, ge: $ge, le: $le,);

        $refProperty = new ReflectionProperty(NumericFieldValidateTestClass::class, 'prop');
        $inputData = new InputData(['prop' => $value]);

        $propertyOperator = PropertyOperator::create($refProperty, $inputData, $field);

        if ($expectException) {
            $this->expectException(ValidationException::class);
        }

        $field->validate($propertyOperator);

        // @phpstan-ignore method.alreadyNarrowedType (例外が発生しなければテストは成功)
        $this->assertTrue(true);
    }
}
