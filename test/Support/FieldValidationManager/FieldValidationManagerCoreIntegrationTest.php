<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Validators\PrimitiveTypeValidator;
use PhpValueObject\Validators\PropertyInitializedValidator;
use PhpValueObject\Validators\PropertyTypeValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use TypeError;

class FieldValidationManagerCoreIntegrationTest extends TestCase
{
    /**
     * コアバリデータ（PropertyInitializedValidator、PropertyTypeValidator、PrimitiveTypeValidator）を
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

        $modelConfig = new ModelConfig();
        $fieldConfig = new FieldConfig();

        $coreValidators = [
            new PropertyInitializedValidator($modelConfig, $fieldConfig, $metadata),
            new PropertyTypeValidator($modelConfig, $fieldConfig, $metadata),
            new PrimitiveTypeValidator($metadata),
        ];

        $manager = FieldValidationManager::createFromProperty($prop, $field, coreValidators: $coreValidators);

        // 正常値でのテスト
        $inputData = new InputData(['testProp' => 'valid_string']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $result = $manager->processValidation($original);
        $this->assertEquals('valid_string', $result->value->value);

        // 不正な型の値でのテスト（StringFieldに対して数値を渡す）
        $inputDataInvalid = new InputData(['testProp' => 123]);
        $originalInvalid = PropertyOperator::create($prop, $inputDataInvalid, $field);

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Cannot assign integer to property');
        $manager->processValidation($originalInvalid);
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

        $modelConfig = new ModelConfig(allowUninitializedProperty: false);
        $fieldConfig = new FieldConfig(allowUninitializedProperty: false);

        $coreValidators = [
            new PropertyInitializedValidator($modelConfig, $fieldConfig, $metadata),
            new PropertyTypeValidator($modelConfig, $fieldConfig, $metadata),
            new PrimitiveTypeValidator($metadata),
        ];

        $manager = FieldValidationManager::createFromProperty($prop, $field, coreValidators: $coreValidators);

        $inputData = new InputData(['testProp' => 'some_value']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $this->expectException(InvalidPropertyStateException::class);
        $this->expectExceptionMessage('is not initialized');
        $manager->processValidation($original);
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

        $modelConfig = new ModelConfig(allowNoneTypeProperty: false);
        $fieldConfig = new FieldConfig(allowNoneTypeProperty: false);

        $coreValidators = [
            new PropertyInitializedValidator($modelConfig, $fieldConfig, $metadata),
            new PropertyTypeValidator($modelConfig, $fieldConfig, $metadata),
            new PrimitiveTypeValidator($metadata),
        ];

        $manager = FieldValidationManager::createFromProperty($prop, $field, [], $coreValidators);

        $inputData = new InputData(['testProp' => 'some_value']);
        $original = PropertyOperator::create($prop, $inputData, $field);

        $this->expectException(InvalidPropertyStateException::class);
        $this->expectExceptionMessage('not allow none property type');
        $manager->processValidation($original);
    }
}
