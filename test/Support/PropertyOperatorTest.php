<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\TypeHint;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use PhpValueObject\Exceptions\ValidationException;
use TypeError;

class PropertyOperatorTest extends TestCase
{
    private ReflectionClass $refClass;

    protected function setUp(): void
    {
        $this->refClass = new ReflectionClass(TestModel::class);
    }

    /**
     * プロパティの初期化状態に応じて、適切な値と型が設定されることを確認
     */
    #[Test]
    #[DataProvider('providePropertyStates')]
    public function testCreateWithDifferentPropertyStates(
        mixed $input,
        ?string $propertyName,
        ?string $defaultFactoryValue,
        PropertyInitializedStatus $expectedStatus,
        PropertyValueType $expectedType,
        mixed $expectedValue,
    ): void {
        $property = $this->refClass->getProperty($propertyName);

        $input = ($input !== null) ? [$propertyName => $input] : [];

        $inputData = new InputData($input);
        $field = new TestField($defaultFactoryValue);

        $operator = PropertyOperator::create($property, $inputData, $field);

        $this->assertSame(TestModel::class, $operator->class);
        $this->assertSame($propertyName, $operator->name);
        $this->assertInstanceOf(TypeHint::class, $operator->typeHints[0]);
        $this->assertSame($expectedStatus, $operator->initializedStatus);
        $this->assertSame($expectedType, $operator->valueType);
        $this->assertSame($expectedValue, $operator->value);
    }

    public static function providePropertyStates(): array
    {
        return [
            '未初期化の場合' => [
                'input' => null,
                'propertyName' => 'name',
                'defaultFactoryValue' => null,
                'expectedStatus' => PropertyInitializedStatus::UNINITIALIZED,
                'expectedType' => PropertyValueType::NULL,
                'expectedValue' => null,
            ],
            'デフォルトファクトリーがある場合' => [
                'input' => null,
                'propertyName' => 'name',
                'defaultFactoryValue' => 'factory value',
                'expectedStatus' => PropertyInitializedStatus::BY_FACTORY,
                'expectedType' => PropertyValueType::STRING,
                'expectedValue' => 'factory value',
            ],
            'デフォルトファクトリーと入力値がある場合' => [
                'input' => 'test input',
                'propertyName' => 'name',
                'defaultFactoryValue' => 'factory value',
                'expectedStatus' => PropertyInitializedStatus::BY_FACTORY,
                'expectedType' => PropertyValueType::STRING,
                'expectedValue' => 'factory value',
            ],
            '入力値がある場合' => [
                'input' => 'test input',
                'propertyName' => 'name',
                'defaultFactoryValue' => null,
                'expectedStatus' => PropertyInitializedStatus::BY_INPUT,
                'expectedType' => PropertyValueType::STRING,
                'expectedValue' => 'test input',
            ],
            'デフォルト値がある場合' => [
                'input' => null,
                'propertyName' => 'default',
                'defaultFactoryValue' => null,
                'expectedStatus' => PropertyInitializedStatus::BY_DEFAULT,
                'expectedType' => PropertyValueType::STRING,
                'expectedValue' => 'test property',
            ],
        ];
    }

    /**
     * getPropertyValueメソッドが正常に値を返すことを確認
     */
    #[Test]
    public function testGetPropertyValueReturnsValueSuccessfully(): void
    {
        $property = $this->refClass->getProperty('name');
        $input = ['name' => 'test value'];
        $inputData = new InputData($input);
        $field = new TestField();

        $operator = PropertyOperator::create($property, $inputData, $field);
        $result = $operator->getPropertyValue($field);

        $this->assertSame('test value', $result);
    }

    /**
     * バリデーションが失敗した場合にValidationExceptionが発生することを確認
     */
    #[Test]
    public function testGetPropertyValueThrowsValidationException(): void
    {
        $property = $this->refClass->getProperty('name');
        $input = ['name' => 'invalid value'];
        $inputData = new InputData($input);
        $field = new ValidationErrorField();

        $operator = PropertyOperator::create($property, $inputData, $field);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $operator->getPropertyValue($field);
    }

    /**
     * プリミティブ型チェックに失敗した場合にTypeErrorが発生することを確認
     */
    #[Test]
    public function testGetPropertyValueThrowsTypeError(): void
    {
        $property = $this->refClass->getProperty('name');
        $input = ['name' => new \stdClass()];
        $inputData = new InputData($input);
        $field = new TestField();

        $operator = PropertyOperator::create($property, $inputData, $field);

        $this->expectException(TypeError::class);
        $operator->getPropertyValue($field);
    }
}

class TestModel
{
    public string $name;
    public string $default = 'test property';
}

class TestField extends BaseField
{
    public function __construct(?string $defaultFactoryValue = null)
    {
        parent::__construct(
            defaultFactory: $defaultFactoryValue === null ? null : fn() => $defaultFactoryValue,
        );
    }

    public function validate(mixed $value): void
    {
    }
}

class ValidationErrorField extends BaseField
{
    public function validate(mixed $value): void
    {
        throw new ValidationException('Validation failed');
    }
}
