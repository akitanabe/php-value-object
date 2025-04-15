<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Fields\Field;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Support\TypeHint;
use ReflectionProperty;

/**
 * テスト用のクラス
 */
class TestClassForMetadata
{
    public string $stringProperty = 'default';
    public ?string $nullableProperty;
    public string|int $unionProperty;
}

class PropertyMetadataTest extends TestCase
{
    /**
     * PropertyMetadata::fromReflectionメソッドのテスト
     *
     * @param array<string, mixed> $inputData 入力データ
     * @param TypeHintType[] $expectedType 期待される型
     */
    #[Test]
    #[DataProvider('reflectionDataProvider')]
    public function testFromReflection(
        string $propertyName,
        array $inputData,
        PropertyInitializedStatus $expectedStatus,
        array $expectedType = [],
    ): void {
        $reflectionProperty = new ReflectionProperty(TestClassForMetadata::class, $propertyName);
        $input = new InputData($inputData);
        $field = new Field();

        $metadata = PropertyMetadata::fromReflection($reflectionProperty, $input, $field);

        $this->assertSame(TestClassForMetadata::class, $metadata->class);
        $this->assertSame($propertyName, $metadata->name);
        $this->assertNotEmpty($metadata->typeHints);
        $this->assertEqualsCanonicalizing($expectedType, array_map(
            static fn(TypeHint $typeHint): TypeHintType => $typeHint->type,
            $metadata->typeHints,
        ));
        $this->assertSame($expectedStatus, $metadata->initializedStatus);
    }

    /**
     * @return array<string, array{
     *   propertyName: string,
     *   inputData: array<string, mixed>,
     *   expectedStatus: PropertyInitializedStatus,
     *   expectedType: TypeHintType[]
     * }>
     */
    public static function reflectionDataProvider(): array
    {
        return [
            '初期化済みプロパティ' => [
                'propertyName' => 'stringProperty',
                'inputData' => [],
                'expectedStatus' => PropertyInitializedStatus::BY_DEFAULT,
                'expectedType' => [TypeHintType::STRING],
            ],
            '未初期化プロパティ' => [
                'propertyName' => 'nullableProperty',
                'inputData' => [],
                'expectedStatus' => PropertyInitializedStatus::UNINITIALIZED,
                'expectedType' => [TypeHintType::STRING],
            ],
            '入力データありの場合' => [
                'propertyName' => 'unionProperty',
                'inputData' => ['unionProperty' => 'test'],
                'expectedStatus' => PropertyInitializedStatus::BY_INPUT,
                'expectedType' => [TypeHintType::STRING, TypeHintType::INT],
            ],
        ];
    }

    /**
     * PropertyMetadataのコンストラクタのテスト
     */
    #[Test]
    public function testConstructor(): void
    {
        $reflectionProperty = new ReflectionProperty(TestClassForMetadata::class, 'stringProperty');
        $typeHints = [];  // 本来はTypeHintsの配列が入るが、テストのため空配列を使用

        $metadata = new PropertyMetadata(
            TestClassForMetadata::class,
            'stringProperty',
            $typeHints,
            PropertyInitializedStatus::BY_DEFAULT,
        );

        $this->assertSame(TestClassForMetadata::class, $metadata->class);
        $this->assertSame('stringProperty', $metadata->name);
        $this->assertSame($typeHints, $metadata->typeHints);
        $this->assertSame(PropertyInitializedStatus::BY_DEFAULT, $metadata->initializedStatus);
    }
}
