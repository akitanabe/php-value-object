<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Support\FieldValidatorStorage;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\SystemValidatorFactory;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Fields\Field;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Core\Validators\InitializationStateValidator;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\PlainValidator;
use PhpValueObject\Validators\WrapValidator;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Validators\ValidatorMode;
use PhpValueObject\Support\FunctionValidatorFactory;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Validators\FunctionalValidator;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\TypeHintType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use ReflectionAttribute;

class FieldValidationManagerComplexOrderTest extends TestCase
{
    private ValidatorDefinitions $validatorDefinitions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validatorDefinitions = (new ValidatorDefinitions)->registerMultiple(
            new ModelConfig(),
            new FieldConfig(),
        );
    }
    /**
     * 全種類のバリデーター（Before、After、Plain、Wrap）を組み合わせたテスト
     */
    #[Test]
    public function testAllValidatorTypesInCombination(): void
    {
        $testClass = new class {
            public static function addAttrBefore(string $value): string
            {
                return $value . '_attr_before';
            }

            public static function addAttrAfter(string $value): string
            {
                return $value . '_attr_after';
            }

            public static function upperCase(string $value): string
            {
                return strtoupper($value);
            }

            public static function addWrapped(string $value): string
            {
                // WrapValidatorは他のバリデータの後、最後に適用される想定だが、
                // 現在のCoR実装ではPlainの後に実行される
                return $value . '_wrapped';
            }

            #[BeforeValidator([self::class, 'addAttrBefore'])]
            #[AfterValidator([self::class, 'addAttrAfter'])]
            #[PlainValidator([self::class, 'upperCase'])]
            #[WrapValidator([self::class, 'addWrapped'])]
            public string $allValidatorsProp;

            // FieldValidator 用メソッド
            #[FieldValidator('allValidatorsProp', ValidatorMode::BEFORE)]
            public static function addFieldBefore(string $value): string
            {
                return $value . '_field_before';
            }

            #[FieldValidator('allValidatorsProp', ValidatorMode::AFTER)]
            public static function addFieldAfter(string $value): string
            {
                return $value . '_field_after';
            }
        };

        $refClass = new ReflectionClass($testClass); // ReflectionClass を使用
        $prop = $refClass->getProperty('allValidatorsProp');
        $field = new StringField();

        // FieldValidatorStorage を生成
        $fieldValidatorStorage = FieldValidatorStorage::createFromClass($refClass);

        // FieldValidatorStoarge から FunctionValidatorFactory を生成
        $functionValidatorFactory = FunctionValidatorFactory::createFromStorage($fieldValidatorStorage, $prop);

        // FunctionValidatorFactory を使用してマネージャーを作成
        $manager = new FieldValidationManager(
            $field,
            $functionValidatorFactory, // FunctionValidatorFactory を渡す
        );

        $inputData = new InputData(['allValidatorsProp' => 'base']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $this->validatorDefinitions->registerMultiple(
            $functionValidatorFactory->createDefinition(),
            $field->getDefinition(),
            $original->metadata,
        );

        $result = $manager->processValidation($original, $this->validatorDefinitions);

        // 実行順: field_before -> attr_before -> plain -> wrap -> attr_after -> field_after
        // field_before: + '_field_before'
        // attr_before: + '_attr_before'
        // plain: upperCase
        // wrap: + '_wrapped' (Plainの後に実行される)
        // attr_after: + '_attr_after'
        // field_after: + '_field_after'
        // base -> base_field_before -> base_field_before_attr_before -> BASE_FIELD_BEFORE_ATTR_BEFORE
        // -> BASE_FIELD_BEFORE_ATTR_BEFORE_wrapped -> BASE_FIELD_BEFORE_ATTR_BEFORE_wrapped_attr_after
        // -> BASE_FIELD_BEFORE_ATTR_BEFORE_wrapped_attr_after_field_after
        $this->assertEquals('BASE_FIELD_BEFORE_ATTR_BEFORE_attr_after_field_after', $result->value->value);
    }

    /**
     * ValidationFunctionWrapHandlerのCoR実装により、バリデータが追加された順序で実行されることを確認するテスト
     */
    #[Test]
    public function testValidatorExecutionOrderWithoutSorting(): void
    {
        $testClass = new class {
            public static function first(string $value): string
            {
                return $value . '_first';
            }

            public static function second(string $value): string // Plain
            {
                return $value . '_second';
            }

            public static function third(string $value): string // After
            {
                return $value . '_third';
            }

            public static function fourth(string $value): string // Wrap
            {
                return $value . '_fourth';
            }

            // 異なるタイプのバリデータを意図的に混在させる
            #[AfterValidator([self::class, 'third'])]  // attr_after
            #[BeforeValidator([self::class, 'first'])] // attr_before
            #[PlainValidator([self::class, 'second'])] // plain
            #[WrapValidator([self::class, 'fourth'])]  // wrap
            public string $mixedValidators;
        };

        $refClass = new ReflectionClass($testClass); // ReflectionClass を使用
        $prop = $refClass->getProperty('mixedValidators');
        $field = new StringField();

        // 属性バリデータを直接取得
        $functionValidators = AttributeHelper::getAttributeInstances(
            $prop,
            FunctionalValidator::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );
        $functionValidatorFactory = new FunctionValidatorFactory([], $functionValidators);

        // FunctionValidatorFactory を使用してマネージャーを作成
        $manager = new FieldValidationManager(
            $field,
            $functionValidatorFactory, // FunctionValidatorFactory を渡す
        );
        $inputData = new InputData(['mixedValidators' => 'base']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $this->validatorDefinitions->registerMultiple(
            $functionValidatorFactory->createDefinition(),
            $field->getDefinition(),
            $original->metadata,
        );

        $result = $manager->processValidation($original, $this->validatorDefinitions);

        // 実行順: attr_before -> plain -> attr_after
        // first -> second -> third
        // WrapValidatorが実行されていないのは仕様：PlainValidatorが実行されると処理が折り返されるため
        // base -> base_first -> base_first_second -> base_first_second_third
        $this->assertEquals('base_first_second_third', $result->value->value);
    }

    /**
     * ValidationFunctionWrapHandlerのCoR実装により、属性バリデータとフィールドバリデータが
     * 追加された順序で実行されることを確認するテスト
     */
    #[Test]
    public function testAttributeAndFieldValidatorOrderWithoutSorting(): void
    {
        $testClass = new class {
            public static function attrFirst(string $value): string // Before
            {
                return $value . '_attr1';
            }

            public static function attrSecond(string $value): string // After
            {
                return $value . '_attr2';
            }

            #[BeforeValidator([self::class, 'attrFirst'])]
            #[AfterValidator([self::class, 'attrSecond'])]
            public string $mixedValidators;

            // FieldValidator 用メソッド
            #[FieldValidator('mixedValidators', ValidatorMode::BEFORE)]
            public static function addField1(string $value): string
            {
                return $value . '_field1';
            }

            #[FieldValidator('mixedValidators', ValidatorMode::AFTER)]
            public static function addField2(string $value): string
            {
                return $value . '_field2';
            }
        };

        $refClass = new ReflectionClass($testClass); // ReflectionClass を使用
        $prop = $refClass->getProperty('mixedValidators');
        $field = new StringField();

        // FieldValidatorStorage を生成
        $fieldValidatorStorage = FieldValidatorStorage::createFromClass($refClass);

        // FieldValidatorStoarge から FunctionValidatorFactory を生成
        $functionValidatorFactory = FunctionValidatorFactory::createFromStorage($fieldValidatorStorage, $prop);

        // FunctionValidatorFactory を使用してマネージャーを作成
        $manager = new FieldValidationManager(
            $field,
            $functionValidatorFactory, // FunctionValidatorFactory を渡す
        );
        $inputData = new InputData(['mixedValidators' => 'base']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $this->validatorDefinitions->registerMultiple(
            $functionValidatorFactory->createDefinition(),
            $field->getDefinition(),
            $original->metadata,
        );

        $result = $manager->processValidation($original, $this->validatorDefinitions);

        // 実行順: field_before -> attr_before -> attr_after -> field_after
        // field1 -> attrFirst -> attrSecond -> field2
        // base -> base_field1 -> base_field1_attr1 -> base_field1_attr1_attr2 -> base_field1_attr1_attr2_field2
        $this->assertEquals('base_field1_attr1_attr2_field2', $result->value->value);
    }

    /**
     * ValidationFunctionWrapHandlerのCoR実装により、コアバリデータを含む全てのバリデータが
     * 追加された順序で実行されることを確認するテスト
     */
    #[Test]
    public function testAllValidatorTypesOrderWithoutSorting(): void
    {
        $testClass = new class {
            public static function attrValidator(string $value): string // Before
            {
                return $value . '_attr';
            }

            #[BeforeValidator([self::class, 'attrValidator'])]
            public string $allValidators;

            // FieldValidator 用メソッド
            #[FieldValidator('allValidators', ValidatorMode::BEFORE)]
            public static function addField(string $value): string
            {
                return $value . '_field';
            }
        };

        $refClass = new ReflectionClass($testClass); // ReflectionClass を使用
        $prop = $refClass->getProperty('allValidators');
        $field = new StringField();

        // FieldValidatorStorage を生成
        $fieldValidatorStorage = FieldValidatorStorage::createFromClass($refClass);

        // FieldValidatorStoarge から FunctionValidatorFactory を生成
        $functionValidatorFactory = FunctionValidatorFactory::createFromStorage($fieldValidatorStorage, $prop);


        // FunctionValidatorFactory を使用してマネージャーを作成
        $manager = new FieldValidationManager(
            $field,
            $functionValidatorFactory // FunctionValidatorFactory を渡す
        );

        $inputData = new InputData(['allValidators' => 'base']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $this->validatorDefinitions->registerMultiple(
            $functionValidatorFactory->createDefinition(),
            $field->getDefinition(),
            $original->metadata,
        );

        $result = $manager->processValidation($original, $this->validatorDefinitions);

        // 想定される実行順序:
        // 1. フィールドBefore (fieldValidator) -> + '_field'
        // 2. 属性Before (attrValidator) -> + '_attr'
        // 3. 追加コアバリデータ (coreValidator) -> 変更なし
        // base -> base_field -> base_field_attr -> base_field_attr
        $this->assertEquals('base_field_attr', $result->value->value);
    }
}
