<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Validators\FieldValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

// テスト用のバリデータクラス
class TestValidatorForFieldValidator
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
}

// テスト対象のクラス（属性なし）
class TestClassForFieldValidator
{
    public string $name;
}

class FieldValidationManagerFieldValidatorTest extends TestCase
{
    private FieldValidationManager $managerWithFieldValidators;
    private ReflectionProperty $property;
    private StringField $field;

    protected function setUp(): void
    {
        $class = new TestClassForFieldValidator();
        $this->property = new ReflectionProperty($class, 'name');
        $this->field = new StringField();

        // FieldValidatorのみを使用したマネージャー
        $beforeValidator = new FieldValidator('name', 'before');
        $beforeValidator->setValidator(fn(string $value) => TestValidatorForFieldValidator::validateLength($value));
        $afterValidator = new FieldValidator('name', 'after');
        $afterValidator->setValidator(fn(string $value) => TestValidatorForFieldValidator::formatName($value));
        $this->managerWithFieldValidators = FieldValidationManager::createFromProperty(
            $this->property,
            $this->field,
            [$beforeValidator, $afterValidator],
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
