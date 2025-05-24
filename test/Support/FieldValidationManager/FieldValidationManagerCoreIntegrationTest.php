<?php

declare(strict_types=1);

namespace PhSculptis\Test\Support\FieldValidationManager;

use TypeError;
use PhSculptis\Config\FieldConfig;
use PhSculptis\Config\ModelConfig;
use PhSculptis\Core\ValidatorDefinitions;
use PhSculptis\Support\TypeHint;
use PhSculptis\Support\FieldValidationManager;
use PhSculptis\Support\InputData;
use PhSculptis\Support\PropertyMetadata;
use PhSculptis\Support\PropertyOperator;
use PhSculptis\Fields\StringField;
use PhSculptis\Core\Validators\PrimitiveTypeValidator;
use PhSculptis\Enums\PropertyInitializedStatus;
use PhSculptis\Enums\TypeHintType;
use PhSculptis\Exceptions\InvalidPropertyStateException;
use PhSculptis\Support\FunctionValidatorFactory;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

class FieldValidationManagerCoreIntegrationTest extends TestCase
{
    /**
     * コアバリデータ（PropertyInitializedValidator、PrimitiveTypeValidator）を
     * 使用した統合テスト
     */
    #[Test]
    public function testWithCoreValidators(): void
    {
        $testClass = new class {
            public string $testProp;
        };

        $prop = new ReflectionProperty($testClass, 'testProp');
        $field = new StringField();

        $metadata = new PropertyMetadata(
            get_class($testClass),
            'testProp',
            [new TypeHint(TypeHintType::STRING, true, false)],
            PropertyInitializedStatus::BY_DEFAULT,
        );

        $validatorDefinitions = (new ValidatorDefinitions())->registerMultiple(
            new ModelConfig(),
            new FieldConfig(),
            $metadata,
            $field->getDefinition(),
        );

        // 空のFunctionValidatorFactoryを作成
        $functionValidatorFactory = new FunctionValidatorFactory([], []);
        $manager = new FieldValidationManager($field, $functionValidatorFactory);

        // 正常値でのテスト
        $inputData = new InputData(['testProp' => 'valid_string']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $result = $manager->processValidation($original, $validatorDefinitions);
        $this->assertEquals('valid_string', $result->value->value);

        // 不正な型の値でのテスト（StringFieldに対して数値を渡す）
        $inputDataInvalid = new InputData(['testProp' => 123]);
        $originalInvalid = PropertyOperator::create($prop, $inputDataInvalid, $field);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Cannot assign integer to property');
        $manager->processValidation($originalInvalid, $validatorDefinitions);
    }

    /**
     * 未初期化プロパティに対するコアバリデータのテスト
     */
    #[Test]
    public function testWithCoreValidatorsForUninitializedProperty(): void
    {
        $testClass = new class {
            public string $testProp;
        };

        $prop = new ReflectionProperty($testClass, 'testProp');
        $field = new StringField();

        $metadata = new PropertyMetadata(
            get_class($testClass),
            'testProp',
            [new TypeHint(TypeHintType::STRING, true, false)],
            PropertyInitializedStatus::UNINITIALIZED,
        );

        $validatorDefinitions = (new ValidatorDefinitions())->registerMultiple(
            new ModelConfig(allowUninitializedProperty: false),
            new FieldConfig(allowUninitializedProperty: false),
            $metadata,
            $field->getDefinition(),
        );

        // 空のFunctionValidatorFactoryを作成
        $functionValidatorFactory = new FunctionValidatorFactory([], []);
        $manager = new FieldValidationManager($field, $functionValidatorFactory);

        $inputData = new InputData(['testProp' => 'some_value']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $this->expectException(InvalidPropertyStateException::class);
        $this->expectExceptionMessage('is not initialized');
        $manager->processValidation($original, $validatorDefinitions);
    }

    /**
     * None型プロパティに対するコアバリデータのテスト
     */
    #[Test]
    public function testWithCoreValidatorsForNoneTypeProperty(): void
    {
        $testClass = new class {
            // @phpstan-ignore missingType.property (None型プロパティのテスト)
            public $testProp;
        };

        $prop = new ReflectionProperty($testClass, 'testProp');
        $field = new StringField();

        $metadata = new PropertyMetadata(
            get_class($testClass),
            'testProp',
            [new TypeHint(TypeHintType::NONE, false, false)],
            PropertyInitializedStatus::BY_DEFAULT,
        );

        // 空のFunctionValidatorFactoryを作成
        $functionValidatorFactory = new FunctionValidatorFactory([], []);

        $manager = new FieldValidationManager($field, $functionValidatorFactory);

        $inputData = new InputData(['testProp' => 'some_value']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $validatorDefinitions = (new ValidatorDefinitions())->registerMultiple(
            new ModelConfig(allowUninitializedProperty: false),
            new FieldConfig(allowUninitializedProperty: false),
            $original->metadata,
            $field->getDefinition(),
        );


        $this->expectException(InvalidPropertyStateException::class);
        $this->expectExceptionMessage('not allow none property type');
        $manager->processValidation($original, $validatorDefinitions);
    }
}
