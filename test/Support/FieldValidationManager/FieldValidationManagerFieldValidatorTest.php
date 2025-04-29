<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\FieldValidatorFactory; // 追加
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Validators\FunctionalValidatorMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass; // 追加
use ReflectionProperty;

// テスト用のバリデータメソッドを持つクラス
class TestClassWithFieldValidators
{
    public string $name;

    #[FieldValidator('name', FunctionalValidatorMode::BEFORE)]
    public static function validateLength(string $value): string
    {
        if (strlen($value) < 3) {
            throw new ValidationException('3文字以上必要です');
        }
        return $value;
    }

    #[FieldValidator('name', FunctionalValidatorMode::AFTER)]
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

    protected function setUp(): void
    {
        // バリデータを持つクラスの Reflection を使用
        $refClass = new ReflectionClass(TestClassWithFieldValidators::class);
        $this->property = $refClass->getProperty('name');
        $this->field = new StringField();

        // FieldValidatorFactory を生成
        $fieldValidatorFactory = FieldValidatorFactory::createFromClass($refClass);

        // FieldValidatorFactory を使用してマネージャーを作成
        $this->managerWithFieldValidators = FieldValidationManager::createFromProperty(
            $this->property,
            $this->field,
            $fieldValidatorFactory, // ファクトリを渡す
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

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithFieldValidators->processValidation($operator);
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

        $result = $this->managerWithFieldValidators->processValidation($original);

        $this->assertNotSame($original, $result);
        $this->assertEquals('john', $original->value->value);
        $this->assertEquals('John', $result->value->value);
        $this->assertEquals($original->metadata->class, $result->metadata->class);
        $this->assertEquals($original->metadata->name, $result->metadata->name);
    }
}
