<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Support\PropertyValue;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

class PropertyValueTest extends TestCase
{
    /**
     * コンストラクタのテスト
     */
    #[Test]
    public function testConstructor(): void
    {
        $value = 'test';
        $valueType = PropertyValueType::STRING;

        $propertyValue = new PropertyValue($value, $valueType);

        $this->assertSame($value, $propertyValue->value);
        $this->assertSame($valueType, $propertyValue->valueType);
    }

    /**
     * fromValueメソッドのテスト
     */
    #[Test]
    #[DataProvider('valueDataProvider')]
    public function testFromValue(mixed $value, PropertyValueType $expectedType): void
    {
        $propertyValue = PropertyValue::fromValue($value);

        $this->assertSame($value, $propertyValue->value);
        $this->assertSame($expectedType, $propertyValue->valueType);
    }

    /**
     * さまざまな値のタイプのテスト用データプロバイダー
     *
     * @return array<string, array{value: mixed, expectedType: PropertyValueType}>
     */
    public static function valueDataProvider(): array
    {
        return [
            '文字列' => [
                'value' => 'test string',
                'expectedType' => PropertyValueType::STRING,
            ],
            '整数' => [
                'value' => 123,
                'expectedType' => PropertyValueType::INT,
            ],
            '浮動小数点数' => [
                'value' => 123.45,
                'expectedType' => PropertyValueType::FLOAT,
            ],
            '真偽値' => [
                'value' => true,
                'expectedType' => PropertyValueType::BOOL,
            ],
            '配列' => [
                'value' => ['a', 'b', 'c'],
                'expectedType' => PropertyValueType::ARRAY ,
            ],
            'オブジェクト' => [
                'value' => new stdClass(),
                'expectedType' => PropertyValueType::OBJECT,
            ],
            'NULL' => [
                'value' => null,
                'expectedType' => PropertyValueType::NULL,
            ],
        ];
    }
}
