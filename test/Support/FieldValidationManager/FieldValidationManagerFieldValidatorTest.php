<?php

declare(strict_types=1);

namespace PhSculptis\Test\Support\FieldValidationManager;

use PhSculptis\Config\FieldConfig;
use PhSculptis\Config\ModelConfig;
use PhSculptis\Core\ValidatorDefinitions;
use PhSculptis\Exceptions\ValidationException;
use PhSculptis\Fields\StringField;
use PhSculptis\Support\FieldValidationManager;
use PhSculptis\Support\FieldValidatorStorage;
use PhSculptis\Support\FunctionValidatorFactory; // 追加
use PhSculptis\Support\InputData;
use PhSculptis\Support\PropertyOperator;
use PhSculptis\Validators\FieldValidator;
use PhSculptis\Validators\ValidatorMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionProperty;

// テスト用のバリデータメソッドを持つクラス
class TestClassWithFieldValidators
{
    public string $name;

    #[FieldValidator('name', ValidatorMode::BEFORE)]
    public static function validateLength(string $value): string
    {
        if (strlen($value) < 3) {
            throw new ValidationException('3文字以上必要です');
        }
        return $value;
    }

    #[FieldValidator('name', ValidatorMode::AFTER)]
    public static function formatName(string $value): string
    {
        return ucfirst($value);
    }
}

class FieldValidationManagerFieldValidatorTest extends TestCase
{
    private FieldValidationManager $managerWithFieldValidators;
    private ReflectionProperty $property;
    private StringField $field;

    private ValidatorDefinitions $validatorDefinitions;

    protected function setUp(): void
    {
        // バリデータを持つクラスの Reflection を使用
        $refClass = new ReflectionClass(TestClassWithFieldValidators::class);
        $this->property = $refClass->getProperty('name');
        $this->field = new StringField();

        // FieldValidatorStorage を生成
        $fieldValidatorStorage = FieldValidatorStorage::createFromClass($refClass);

        // FieldValidatorStoarge から FunctionValidatorFactory を生成
        $functionValidatorFactory = FunctionValidatorFactory::createFromStorage(
            $fieldValidatorStorage,
            $this->property,
        );

        // FunctionValidatorFactory を使用してマネージャーを作成
        $this->managerWithFieldValidators = new FieldValidationManager(
            $this->field,
            $functionValidatorFactory, // FunctionValidatorFactory を渡す
        );

        $this->validatorDefinitions = (new ValidatorDefinitions())->registerMultiple(
            new ModelConfig(),
            new FieldConfig(),
            $functionValidatorFactory->createDefinition(),
            $this->field->getDefinition(),
        );

    }

    /**
     * PropertyOperatorを使用したFieldValidatorのバリデーション失敗テスト
     * 入力値の長さが3文字未満の場合、ValidationExceptionが発生する
     */
    #[Test]
    public function testFieldValidation(): void
    {
        $inputData = new InputData(['name' => 'ab']);
        $operator = PropertyOperator::create($this->property, $inputData, $this->field);

        $this->validatorDefinitions->register($operator->metadata);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithFieldValidators->processValidation($operator, $this->validatorDefinitions);
    }

    /**
     * PropertyOperatorを使用したFieldValidatorのバリデーション成功テスト
     * 値が変更された場合は新しいPropertyOperatorが返される
     */
    #[Test]
    public function testFieldValidationSuccess(): void
    {
        $inputData = new InputData(['name' => 'john']);
        $original = PropertyOperator::create($this->property, $inputData, $this->field);

        $this->validatorDefinitions->register($original->metadata);
        $result = $this->managerWithFieldValidators->processValidation($original, $this->validatorDefinitions);

        $this->assertNotSame($original, $result);
        $this->assertEquals('john', $original->value->value);
        $this->assertEquals('John', $result->value->value);
        $this->assertEquals($original->metadata->class, $result->metadata->class);
        $this->assertEquals($original->metadata->name, $result->metadata->name);
    }
}
