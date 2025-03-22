<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use DateTime;
use stdClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\ListField;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use ReflectionProperty;

class ListFieldValidateTestClass
{
    // @phpstan-ignore missingType.iterableValue
    public array $prop;
}

class ListFieldTest extends TestCase
{
    /**
     * @return array<string, array{
     *   value: mixed,
     *   type: ?string,
     *   expectException: bool
     * }>
     */
    public static function validateDataProvider(): array
    {
        return [
            '型指定なしで空の配列の場合は検証が成功する' => [
                'value' => [],
                'type' => null,
                'expectException' => false,
            ],
            '型指定なしでリストの場合は検証が成功する' => [
                'value' => [1, "2", new stdClass()],
                'type' => null,
                'expectException' => false,
            ],
            '型指定なしで連想配列の場合は例外が発生する' => [
                'value' => ['a' => 1, 'b' => 2],
                'type' => null,
                'expectException' => true,
            ],
            '整数のリストの場合は検証が成功する' => [
                'value' => [1, 2, 3],
                'type' => 'int',
                'expectException' => false,
            ],
            '整数以外が含まれる場合は例外が発生する' => [
                'value' => [1, "2", 3],
                'type' => 'int',
                'expectException' => true,
            ],
            '文字列のリストの場合は検証が成功する' => [
                'value' => ["a", "b", "c"],
                'type' => 'string',
                'expectException' => false,
            ],
            'オブジェクトのリストの場合は検証が成功する' => [
                'value' => [new DateTime(), new DateTime()],
                'type' => DateTime::class,
                'expectException' => false,
            ],
            '異なるクラスのオブジェクトが含まれる場合は例外が発生する' => [
                'value' => [new DateTime(), new stdClass()],
                'type' => DateTime::class,
                'expectException' => true,
            ],
            '配列以外の場合は例外が発生する' => [
                'value' => 'not an array',
                'type' => null,
                'expectException' => true,
            ],
            'nullの場合は例外が発生する' => [
                'value' => null,
                'type' => null,
                'expectException' => true,
            ],
            '真偽値のリストの場合は検証が成功する' => [
                'value' => [true, false, true],
                'type' => 'bool',
                'expectException' => false,
            ],
            '浮動小数点数のリストの場合は検証が成功する' => [
                'value' => [1.1, 2.2, 3.3],
                'type' => 'float',
                'expectException' => false,
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param ?string $type
     * @param bool $expectException
     */
    #[Test]
    #[DataProvider('validateDataProvider')]
    public function testValidate(mixed $value, ?string $type, bool $expectException): void
    {
        $field = new ListField(type: $type);

        $refProperty = new ReflectionProperty(ListFieldValidateTestClass::class, 'prop');
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
