<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Support\PropertyValue;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Core\Validators\IdenticalValidator;
use PhpValueObject\Validators\Validatorable;

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
        $validationManager = FieldValidationManager::createFromProperty($property, $field);

        $operator = PropertyOperator::create($property, $inputData, $field);

        // 新しい構造に対応したアサーション
        $this->assertInstanceOf(PropertyMetadata::class, $operator->metadata);
        $this->assertInstanceOf(PropertyValue::class, $operator->value);

        $this->assertSame(TestModel::class, $operator->metadata->class);
        $this->assertSame($propertyName, $operator->metadata->name);
        $this->assertInstanceOf(TypeHint::class, $operator->metadata->typeHints[0]);
        $this->assertSame($expectedStatus, $operator->metadata->initializedStatus);
        $this->assertSame($expectedType, $operator->value->valueType);
        $this->assertSame($expectedValue, $operator->value->value);
    }

    /**
     * withValueメソッドのテスト
     */
    #[Test]
    public function testWithValue(): void
    {
        $property = $this->refClass->getProperty('name');
        $inputData = new InputData(['name' => 'original']);
        $field = new TestField();

        $operator = PropertyOperator::create($property, $inputData, $field);
        $newOperator = $operator->withValue('modified');

        // 元のオペレーターは変更されていない
        $this->assertSame('original', $operator->value->value);
        $this->assertSame(PropertyValueType::STRING, $operator->value->valueType);

        // 新しいオペレーターには新しい値が設定されている
        $this->assertSame('modified', $newOperator->value->value);
        $this->assertSame(PropertyValueType::STRING, $newOperator->value->valueType);

        // メタデータは共有されている
        $this->assertSame($operator->metadata, $newOperator->metadata);
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

    public function getValidator(): Validatorable
    {
        return new IdenticalValidator();
    }
}

class ValidationErrorField extends BaseField
{
    public function getValidator(): Validatorable
    {
        $validator = new class implements Validatorable {
            public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
            {
                throw new ValidationException('Validation failed');
            }
        };

        return $validator;
    }
}
