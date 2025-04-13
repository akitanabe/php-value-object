<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use PhpValueObject\Exceptions\ValidationException;
use TypeError;
use stdClass;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

class PropertyOperatorTest extends TestCase
{
    /** @var ReflectionClass<TestModel> */
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
        string $propertyName,
        ?string $defaultFactoryValue,
        PropertyInitializedStatus $expectedStatus,
        PropertyValueType $expectedType,
        mixed $expectedValue,
    ): void {
        $property = $this->refClass->getProperty($propertyName);

        $input = ($input !== null) ? [$propertyName => $input] : [];

        $inputData = new InputData($input);
        $field = new TestField($defaultFactoryValue);
        $validationManager = FieldValidationManager::createFromProperty($property);

        $operator = PropertyOperator::create($property, $inputData, $field);

        $this->assertSame(TestModel::class, $operator->class);
        $this->assertSame($propertyName, $operator->name);
        $this->assertInstanceOf(TypeHint::class, $operator->typeHints[0]);
        $this->assertSame($expectedStatus, $operator->initializedStatus);
        $this->assertSame($expectedType, $operator->valueType);
        $this->assertSame($expectedValue, $operator->value);
    }

    /**
     * @return array<string, array{
     *   input: mixed,
     *   propertyName: string,
     *   defaultFactoryValue: string|null,
     *   expectedStatus: PropertyInitializedStatus,
     *   expectedType: PropertyValueType,
     *   expectedValue: mixed
     * }>
     */
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
        $validationManager = FieldValidationManager::createFromProperty($property);

        $operator = PropertyOperator::create($property, $inputData, $field);
        $result = $operator->getPropertyValue($field, $validationManager);

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
        $validationManager = FieldValidationManager::createFromProperty($property);

        $operator = PropertyOperator::create($property, $inputData, $field);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $operator->getPropertyValue($field, $validationManager);
    }

    /**
     * プリミティブ型チェックに失敗した場合にTypeErrorが発生することを確認
     */
    #[Test]
    public function testGetPropertyValueThrowsTypeError(): void
    {
        $property = $this->refClass->getProperty('name');
        $input = ['name' => new stdClass()];
        $inputData = new InputData($input);
        $field = new TestField();
        $validationManager = FieldValidationManager::createFromProperty($property);

        $operator = PropertyOperator::create($property, $inputData, $field);

        $this->expectException(TypeError::class);
        $operator->getPropertyValue($field, $validationManager);
    }

    /**
     * BeforeValidatorが正しく実行されることを確認
     */
    #[Test]
    public function testBeforeValidatorIsExecuted(): void
    {
        $property = $this->refClass->getProperty('validatedBeforeValue');
        $input = ['validatedBeforeValue' => 'a'];
        $inputData = new InputData($input);
        $field = new TestField();
        $validationManager = FieldValidationManager::createFromProperty($property);

        $operator = PropertyOperator::create($property, $inputData, $field);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $operator->getPropertyValue($field, $validationManager);
    }

    /**
     * AfterValidatorが正しく実行されることを確認
     */
    #[Test]
    public function testAfterValidatorIsExecutedDuringGetPropertyValue(): void
    {
        $property = $this->refClass->getProperty('validatedAfterValue');
        $input = ['validatedAfterValue' => 'john'];
        $inputData = new InputData($input);
        $field = new TestField();
        $validationManager = FieldValidationManager::createFromProperty($property);

        $operator = PropertyOperator::create($property, $inputData, $field);
        $result = $operator->getPropertyValue($field, $validationManager);

        $this->assertSame('John', $result);
    }
}

class TestModel
{
    public string $name;
    public string $default = 'test property';

    #[BeforeValidator([TestValidator::class, 'validateLength'])]
    public string $validatedBeforeValue;

    #[AfterValidator([TestValidator::class, 'formatName'])]
    public string $validatedAfterValue;
}

class TestField extends BaseField
{
    public function __construct(?string $defaultFactoryValue = null)
    {
        parent::__construct(
            defaultFactory: $defaultFactoryValue === null ? null : fn() => $defaultFactoryValue,
        );
    }

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        return $value;
    }
}

class ValidationErrorField extends BaseField
{
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        throw new ValidationException('Validation failed');
    }
}
