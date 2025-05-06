<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\FieldValidatorStorage;
use PhpValueObject\Support\FunctionValidatorFactory;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\PlainValidator;
use PhpValueObject\Validators\WrapValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

// テスト用のバリデータクラス
class TestValidatorForAttribute
{
    public static function validateLength(string $value): string
    {
        if (strlen($value) < 3) {
            throw new ValidationException('3文字以上必要です');
        }
        return $value;
    }

    public static function formatName(string $value): string
    {
        return ucfirst($value);
    }

    public static function validateAndFormat(string $value): string
    {
        if (strlen($value) < 4) {
            throw new ValidationException('4文字以上必要です');
        }
        return strtoupper($value);
    }

    public static function toLowerCase(string $value): string
    {
        return strtolower($value);
    }
}

// テスト対象の属性を持つクラス
class TestClassForAttribute
{
    #[BeforeValidator([TestValidatorForAttribute::class, 'validateLength'])]
    #[AfterValidator([TestValidatorForAttribute::class, 'formatName'])]
    public string $name;

    #[PlainValidator([TestValidatorForAttribute::class, 'validateAndFormat'])]
    public string $plainValidated;

    #[WrapValidator([TestValidatorForAttribute::class, 'toLowerCase'])]
    public string $wrappedValue;
}

class FieldValidationManagerAttributeTest extends TestCase
{
    private FieldValidationManager $managerWithAttributes;
    private FieldValidationManager $managerWithPlain;
    private FieldValidationManager $managerWithWrap;
    private ReflectionProperty $property;
    private ReflectionProperty $plainProperty;
    private ReflectionProperty $wrapProperty;
    private StringField $field;

    private ValidatorDefinitions $validatorDefinitions;

    private array $managerFunctionValidatorDefinitions = [];

    protected function setUp(): void
    {
        $class = new TestClassForAttribute();
        $this->property = new ReflectionProperty($class, 'name');
        $this->plainProperty = new ReflectionProperty($class, 'plainValidated');
        $this->wrapProperty = new ReflectionProperty($class, 'wrappedValue');
        $this->field = new StringField();

        $fieldValidatorStorage = new FieldValidatorStorage();

        // 属性のみを使用したマネージャー（name プロパティ用）
        $nameValidatorFactory = FunctionValidatorFactory::createFromStorage($fieldValidatorStorage, $this->property);
        $this->managerWithAttributes = new FieldValidationManager(
            $this->field,
            $nameValidatorFactory,
        );
        $this->managerFunctionValidatorDefinitions['name'] = $nameValidatorFactory->createDefinition();

        // PlainValidator用のマネージャー
        $plainValidatorFactory = FunctionValidatorFactory::createFromStorage(
            $fieldValidatorStorage,
            $this->plainProperty,
        );
        $this->managerWithPlain = new FieldValidationManager(
            $this->field,
            $plainValidatorFactory,
        );
        $this->managerFunctionValidatorDefinitions['plain'] = $plainValidatorFactory->createDefinition();

        // WrapValidator用のマネージャー
        $wrapValidatorFactory = FunctionValidatorFactory::createFromStorage(
            $fieldValidatorStorage,
            $this->wrapProperty,
        );
        $this->managerWithWrap = new FieldValidationManager(
            $this->field,
            $wrapValidatorFactory,
        );
        $this->managerFunctionValidatorDefinitions['wrap'] = $wrapValidatorFactory->createDefinition();

        $this->validatorDefinitions = (new ValidatorDefinitions())->registerMultiple(
            new ModelConfig(),
            new FieldConfig(),
            $this->field->getDefinition(),
        );
    }

    /**
     * PropertyOperatorを使用したバリデーション失敗のテスト
     * 3文字未満の入力を検証した場合、ValidationExceptionが発生する
     */
    #[Test]
    public function testValidationThrowsException(): void
    {
        $inputData = new InputData(['name' => 'ab']);
        $operator = PropertyOperator::create($this->property, $inputData, $this->field);

        $this->validatorDefinitions->registerMultiple(
            $operator->metadata,
            $this->managerFunctionValidatorDefinitions['name'],

        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithAttributes->processValidation($operator, $this->validatorDefinitions);
    }

    /**
     * PropertyOperatorを使用したバリデーション成功のテスト
     * 値が変更された場合は新しいPropertyOperatorが返される
     */
    #[Test]
    public function testValidationSuccess(): void
    {
        $inputData = new InputData(['name' => 'abc']);
        $original = PropertyOperator::create($this->property, $inputData, $this->field);

        $this->validatorDefinitions->registerMultiple(
            $original->metadata,
            $this->managerFunctionValidatorDefinitions['name'],
        );

        $result = $this->managerWithAttributes->processValidation($original, $this->validatorDefinitions);

        $this->assertNotSame($original, $result);
        $this->assertEquals('abc', $original->value->value);
        $this->assertEquals('Abc', $result->value->value);
        $this->assertEquals($original->metadata->class, $result->metadata->class);
        $this->assertEquals($original->metadata->name, $result->metadata->name);
    }

    /**
     * PlainValidatorを使用したバリデーションのテスト
     * 検証と変換の両方を行う
     */
    #[Test]
    public function testPlainValidation(): void
    {
        $inputData = new InputData(['plainValidated' => 'abc']);
        $operator = PropertyOperator::create($this->plainProperty, $inputData, $this->field);

        $this->validatorDefinitions->registerMultiple(
            $operator->metadata,
            $this->managerFunctionValidatorDefinitions['plain'],
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('4文字以上必要です');
        $this->managerWithPlain->processValidation($operator, $this->validatorDefinitions);
    }

    /**
     * PlainValidatorを使用したバリデーション成功のテスト
     */
    #[Test]
    public function testPlainValidationSuccess(): void
    {
        $inputData = new InputData(['plainValidated' => 'test']);
        $original = PropertyOperator::create($this->plainProperty, $inputData, $this->field);

        $this->validatorDefinitions->registerMultiple(
            $original->metadata,
            $this->managerFunctionValidatorDefinitions['plain'],
        );

        $result = $this->managerWithPlain->processValidation($original, $this->validatorDefinitions);

        $this->assertNotSame($original, $result);
        $this->assertEquals('test', $original->value->value);
        $this->assertEquals('TEST', $result->value->value);
    }

    /**
     * WrapValidatorを使用したバリデーションのテスト
     */
    #[Test]
    public function testWrapValidation(): void
    {
        $inputData = new InputData(['wrappedValue' => 'TEST']);
        $original = PropertyOperator::create($this->wrapProperty, $inputData, $this->field);

        $this->validatorDefinitions->registerMultiple(
            $original->metadata,
            $this->managerFunctionValidatorDefinitions['wrap'],
        );


        $result = $this->managerWithWrap->processValidation($original, $this->validatorDefinitions);

        $this->assertNotSame($original, $result);
        $this->assertEquals('TEST', $original->value->value);
        $this->assertEquals('test', $result->value->value);
    }
}
