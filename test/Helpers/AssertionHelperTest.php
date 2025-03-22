<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Helpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Fields\Field;
use PhpValueObject\Helpers\AssertionHelper;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionProperty;
use PhpValueObject\BaseModel;
use stdClass;

// テスト用のクラス定義
final class FinalClass extends BaseModel {}
class InheritableClass extends BaseModel {}

/**
 * プロパティの状態をテストするためのクラス
 */
class TestProperty
{
    public ?string $uninitialized;
    public string $initialized = 'default';
    public mixed $mixedType = '';
    public int|string $unionType = 'test';

    /** @var object */
    public $noneType;

    public function __construct()
    {
        $this->noneType = new stdClass();
    }
}

/**
 * @internal
 */
class AssertionHelperTest extends TestCase
{
    private Field $field;

    protected function setUp(): void
    {
        $this->field = new Field();
    }


    /**
     * @param array<string|int, mixed> $inputData
     * @return PropertyOperator
     */
    private function createPropertyOperator(string $propertyName, array $inputData = []): PropertyOperator
    {
        $property = new ReflectionProperty(TestProperty::class, $propertyName);

        return PropertyOperator::create($property, new InputData($inputData), $this->field);
    }

    /**
     * @return array<string, array{model:bool,field:bool}>
     */
    public static function configAllowPatternDataProvider(): array
    {
        return [
            'ModelConfig(true),FieldConfig(true)' => [
                'model' => true,
                'field' => true,
            ],
            'ModelConfig(true),FieldConfig(false)' => [
                'model' => true,
                'field' => false,
            ],
            'ModelConfig(false),FieldConfig(true)' => [
                'model' => true,
                'field' => false,
            ],
        ];
    }

    /**
     * クラスがfinalでなく、継承が許可されていない場合に例外をスローすることをテスト
     */
    #[Test]
    public function assertInheritableClassThrowsExceptionWhenNotFinalAndInheritanceNotAllowed(): void
    {
        $this->expectException(InheritableClassException::class);

        $refClass = new ReflectionClass(InheritableClass::class);
        $modelConfig = new ModelConfig(allowInheritableClass: false);

        AssertionHelper::assertInheritableClass($refClass, $modelConfig);
    }

    /**
     * クラスがfinalの場合、継承可否の設定に関係なく例外をスローしないことをテスト
     */
    #[Test]
    public function assertInheritableClassDoesNotThrowExceptionWhenFinal(): void
    {
        $refClass = new ReflectionClass(FinalClass::class);
        $modelConfig = new ModelConfig(allowInheritableClass: false);

        AssertionHelper::assertInheritableClass($refClass, $modelConfig);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true); // アサーションが必要なため
    }

    /**
     * 継承が許可されている場合、finalでないクラスでも例外をスローしないことをテスト
     */
    #[Test]
    public function assertInheritableClassDoesNotThrowExceptionWhenInheritanceAllowed(): void
    {
        $refClass = new ReflectionClass(InheritableClass::class);
        $modelConfig = new ModelConfig(allowInheritableClass: true);

        AssertionHelper::assertInheritableClass($refClass, $modelConfig);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true); // アサーションが必要なため
    }

    /**
     * 未初期化のプロパティが許可されていない場合に例外をスローすることをテスト
     */
    #[Test]
    public function assertInvalidPropertyStateThrowsExceptionForUninitializedPropertyWhenNotAllowed(): void
    {
        $this->expectException(InvalidPropertyStateException::class);

        $modelConfig = new ModelConfig();
        $fieldConfig = new FieldConfig();
        $propertyOperator = $this->createPropertyOperator('uninitialized');

        AssertionHelper::assertInvalidPropertyStateOrSkip($modelConfig, $fieldConfig, $propertyOperator);
    }

    /**
     * 未初期化のプロパティが許可されている場合にtrueを返すことをテスト
     */
    #[Test]
    #[DataProvider('configAllowPatternDataProvider')]
    public function assertInvalidPropertyStateReturnsSkipForUninitializedPropertyWhenAllowed(
        bool $model,
        bool $field,
    ): void {
        $modelConfig = new ModelConfig(allowUninitializedProperty: $model);
        $fieldConfig = new FieldConfig(allowUninitializedProperty: $field);
        $propertyOperator = $this->createPropertyOperator('uninitialized');

        $result = AssertionHelper::assertInvalidPropertyStateOrSkip($modelConfig, $fieldConfig, $propertyOperator);

        $this->assertTrue($result);
    }

    /**
     * 型の指定がないプロパティが許可されていない場合に例外をスローすることをテスト
     */
    #[Test]
    public function assertInvalidPropertyStateThrowsExceptionForNoneTypePropertyWhenNotAllowed(): void
    {
        $this->expectException(InvalidPropertyStateException::class);

        $modelConfig = new ModelConfig();
        $fieldConfig = new FieldConfig();
        $propertyOperator = $this->createPropertyOperator('noneType');

        AssertionHelper::assertInvalidPropertyStateOrSkip($modelConfig, $fieldConfig, $propertyOperator);
    }

    /**
     * 型の指定がないプロパティが許可されている場合にtrueを返すことをテスト
     */
    #[Test]
    #[DataProvider('configAllowPatternDataProvider')]
    public function assertInvalidPropertyStateReturnsSkipForNoneTypePropertyWhenAllowed(bool $model, bool $field): void
    {
        $modelConfig = new ModelConfig(allowNoneTypeProperty: $model);
        $fieldConfig = new FieldConfig(allowNoneTypeProperty: $field);
        $propertyOperator = $this->createPropertyOperator('noneType');

        AssertionHelper::assertInvalidPropertyStateOrSkip($modelConfig, $fieldConfig, $propertyOperator);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true);
    }

    /**
     * mixed型のプロパティが許可されている場合にtrueを返すことをテスト
     */
    #[Test]
    #[DataProvider('configAllowPatternDataProvider')]
    public function assertInvalidPropertyStateReturnsSkipForMixedTypePropertyWhenAllowed(bool $model, bool $field): void
    {
        $modelConfig = new ModelConfig(allowMixedTypeProperty: $model);
        $fieldConfig = new FieldConfig(allowMixedTypeProperty: $field);
        $propertyOperator = $this->createPropertyOperator('mixedType');

        AssertionHelper::assertInvalidPropertyStateOrSkip($modelConfig, $fieldConfig, $propertyOperator);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true);
    }

    /**
     * mixed型のプロパティが許可されていない場合に例外をスローすることをテスト
     */
    #[Test]
    public function assertInvalidPropertyStateThrowsExceptionForMixedTypePropertyWhenNotAllowed(): void
    {
        $this->expectException(InvalidPropertyStateException::class);

        $modelConfig = new ModelConfig();
        $fieldConfig = new FieldConfig();
        $propertyOperator = $this->createPropertyOperator('mixedType');

        AssertionHelper::assertInvalidPropertyStateOrSkip($modelConfig, $fieldConfig, $propertyOperator);
    }

    /**
     * Union型のプロパティに対してプリミティブ型チェックをスキップすることをテスト
     */
    #[Test]
    public function assertPrimitiveTypeSkipsForUnionTypeWithObjectValue(): void
    {
        $propertyOperator = $this->createPropertyOperator('unionType');

        AssertionHelper::assertPrimitiveType($propertyOperator);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true); // アサーションが必要なため
    }

    /**
     * プリミティブ型でないプロパティに対して型チェックをスキップすることをテスト
     */
    #[Test]
    public function assertPrimitiveTypeSkipsWhenNoPrimitiveTypes(): void
    {
        $propertyOperator = $this->createPropertyOperator('noneType');

        AssertionHelper::assertPrimitiveType($propertyOperator);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true); // アサーションが必要なため
    }

    /**
     * プロパティの型と入力値の型が一致する場合に例外をスローしないことをテスト
     */
    #[Test]
    public function assertPrimitiveTypeDoesNotThrowExceptionWhenTypeMatches(): void
    {
        $propertyOperator = $this->createPropertyOperator('initialized', ['initialized' => 'test']);

        AssertionHelper::assertPrimitiveType($propertyOperator);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true); // アサーションが必要なため
    }
}
