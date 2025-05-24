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
use PhSculptis\Validators\AfterValidator;
use PhSculptis\Validators\BeforeValidator;
use PhSculptis\Validators\FieldValidator;
use PhSculptis\Validators\ValidatorMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass; // 追加
use ReflectionProperty;

// テスト用のバリデータクラス (属性とFieldValidatorの両方で使用)
class TestClassForCombined
{
    #[BeforeValidator([self::class, 'validateLength'])] // 属性バリデータ
    #[AfterValidator([self::class, 'formatName'])]   // 属性バリデータ
    public string $name;

    // 属性バリデータ用メソッド
    public static function validateLength(string $value): string
    {
        if (strlen($value) < 3) {
            throw new ValidationException('3文字以上必要です');
        }
        return $value;
    }

    // 属性バリデータ用メソッド
    public static function formatName(string $value): string
    {
        return ucfirst($value);
    }

    // FieldValidator用メソッド
    #[FieldValidator('name', ValidatorMode::BEFORE)]
    public static function validateLengthStrict(string $value): string
    {
        if (strlen($value) <= 5) {
            throw new ValidationException('6文字以上必要です');
        }
        return $value;
    }
}

class FieldValidationManagerCombinedTest extends TestCase
{
    private FieldValidationManager $managerWithBoth;
    private ReflectionProperty $property;
    private StringField $field;

    private ValidatorDefinitions $validatorDefinitions;

    protected function setUp(): void
    {
        // バリデータを持つクラスの Reflection を使用
        $refClass = new ReflectionClass(TestClassForCombined::class);
        $this->property = $refClass->getProperty('name');
        $this->field = new StringField();

        // FieldValidatorStoarge を生成
        $fieldValidatorStorage = FieldValidatorStorage::createFromClass($refClass);

        $functionValidatorFactory = FunctionValidatorFactory::createFromStorage(
            $fieldValidatorStorage,
            $this->property,
        );

        // FunctionValidatorFactory を使用してマネージャーを作成
        $this->managerWithBoth = new FieldValidationManager(
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
     * PropertyOperatorを使用した属性とFieldValidatorの組み合わせテスト
     * BeforeValidator属性とFieldValidatorが両方適用される
     * 最初のバリデーション（3文字以上）は通過するが、2番目のバリデーション（6文字以上）で失敗
     */
    #[Test]
    public function testCombinedValidation(): void
    {
        $inputData = new InputData(['name' => 'abcde']);
        $operator = PropertyOperator::create($this->property, $inputData, $this->field);

        $this->validatorDefinitions->register($operator->metadata);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('6文字以上必要です');
        $this->managerWithBoth->processValidation($operator, $this->validatorDefinitions);
    }

    /**
     * PropertyOperatorを使用した属性とFieldValidatorの組み合わせテスト（成功ケース）
     * 全てのバリデーションを通過し、新しいPropertyOperatorが返される
     */
    #[Test]
    public function testCombinedValidationSuccess(): void
    {
        $inputData = new InputData(['name' => 'abcdef']);
        $original = PropertyOperator::create($this->property, $inputData, $this->field);
        $this->validatorDefinitions->register($original->metadata);

        $result = $this->managerWithBoth->processValidation($original, $this->validatorDefinitions);

        $this->assertNotSame($original, $result);
        $this->assertEquals('abcdef', $original->value->value);
        // 属性のAfterValidator([TestValidatorForCombined::class, 'formatName']) と
        // FieldValidator の before が適用される。
        // 実行順: attr_before -> field_before -> field_after -> attr_after
        // このテストケースでは attr_before([validateLength]), field_before([>5]), attr_after([formatName])
        // よって 'abcdef' -> 'abcdef' -> 'abcdef' -> 'Abcdef'
        $this->assertEquals('Abcdef', $result->value->value);
        $this->assertEquals($original->metadata->class, $result->metadata->class);
        $this->assertEquals($original->metadata->name, $result->metadata->name);
    }
}
