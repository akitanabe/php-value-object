<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Helpers;

use PhpValueObject\Fields\Field;
use PhpValueObject\Helpers\PropertyHelper;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Support\InputData;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use UnexpectedValueException;
use UnhandledMatchError;
use stdClass;

class Factory
{
    public function __invoke(): string
    {
        return 'factoryValue';
    }
}


class GetValueTestObject
{
    public string $getProperty = 'defaultValue';
}

class GetInitializedStatusTestObject
{
    public string $defaultProperty = 'defaultValue';
    public string $uninitializedProperty;
}

interface typeA
{
    public function isA(): bool;
}

interface typeB
{
    public function isB(): bool;
}

class GetTypeHintsTestObject
{
    public mixed $oneType;
    public string|int $unionType;
    public typeA&typeB $interserctionType;
}

class PropertyHelperTest extends TestCase
{
    #[Test]
    public function getValue(): void
    {
        foreach (PropertyInitializedStatus::cases() as $status) {
            $refProperty = new ReflectionProperty(GetValueTestObject::class, 'getProperty');
            $inputData = new InputData(['getProperty' => 'inputValue']);
            $field = new Field(defaultFactory: Factory::class);

            $expected = match ($status) {
                PropertyInitializedStatus::BY_FACTORY => 'factoryValue',
                PropertyInitializedStatus::BY_INPUT => 'inputValue',
                default => 'defaultValue',
            };

            $value = PropertyHelper::getValue($status, $refProperty, $inputData, $field);

            $this->assertSame($expected, $value);
        }

    }

    #[Test]
    public function getValueType(): void
    {
        foreach (PropertyValueType::cases() as $type) {
            try {
                $value = match ($type) {
                    PropertyValueType::STRING => 'string',
                    PropertyValueType::INT => 1,
                    PropertyValueType::FLOAT => 1.1,
                    PropertyValueType::BOOL => true,
                    PropertyValueType::ARRAY => [],
                    PropertyValueType::OBJECT => new stdClass(),
                    PropertyValueType::NULL => null,
                };


            } catch (UnhandledMatchError $e) {
                // resource、resource (closed)、unknown typeはテスト対象外
                continue;
            }

            $result = PropertyHelper::getValueType($value);

            $this->assertSame($type, $result);
        }
    }

    #[Test]
    public function getInitializedStatusThrowsUnexpectedValueException(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $refProperty = new ReflectionProperty(GetInitializedStatusTestObject::class, 'defaultProperty');
        $inputData = new InputData([]);
        $field = new Field(defaultFactory: Factory::class);


        PropertyHelper::getInitializedStatus($refProperty, $inputData, $field);
    }

    #[Test]
    public function getInitializedStatus(): void
    {
        $testSet = [
            [
                'expected' => PropertyInitializedStatus::BY_FACTORY,
                'defaultFactory' => Factory::class,
                'inputs' => ['uninitializedProperty' => 'inputValue'],
                'property' => 'uninitializedProperty',
            ],
            [
                'expected' => PropertyInitializedStatus::BY_INPUT,
                'defaultFactory' => null,
                'inputs' => ['defaultProperty' => 'inputValue'],
                'property' => 'defaultProperty',
            ],
            [
                'expected' => PropertyInitializedStatus::BY_DEFAULT,
                'defaultFactory' => null,
                'inputs' => [],
                'property' => 'defaultProperty',
            ],
            [
                'expected' => PropertyInitializedStatus::UNINITIALIZED,
                'defaultFactory' => null,
                'inputs' => [],
                'property' => 'uninitializedProperty',
            ],
        ];

        foreach ($testSet as $tests) {
            $expected = $tests['expected'];
            $refProperty = new ReflectionProperty(GetInitializedStatusTestObject::class, $tests['property']);

            $inputData = new InputData($tests['inputs']);
            $field = new Field(defaultFactory: $tests['defaultFactory']);

            $result = PropertyHelper::getInitializedStatus($refProperty, $inputData, $field);

            $this->assertSame($expected, $result);
        }


    }

    #[Test]
    public function getTypeHints(): void
    {
        $testSet = [
            [
                'property' => 'oneType',
                'expected' => 1,
            ],
            [
                'property' => 'unionType',
                'expected' => 2,
            ],
            [
                'property' => 'interserctionType',
                'expected' => 1,
            ],
        ];

        foreach ($testSet as $test) {
            $refProperty = new ReflectionProperty(GetTypeHintsTestObject::class, $test['property']);
            $result = PropertyHelper::getTypeHints($refProperty);

            $this->assertCount($test['expected'], $result);
        }
    }


}
