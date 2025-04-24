<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\FieldValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

// テスト用のバリデータクラス
class TestValidatorForOrder
{
    public static function validateLength(string $value): string
    {
        // このテストでは例外を投げない
        return $value;
    }

    public static function formatName(string $value): string
    {
        return ucfirst($value);
    }

    public static function addAttrBefore(string $value): string
    {
        return $value . '_attr_before';
    }

    public static function addAttrAfter(string $value): string
    {
        return $value . '_attr_after';
    }
}

// テスト対象の属性を持つクラス
class TestClassForOrder
{
    #[BeforeValidator([TestValidatorForOrder::class, 'validateLength'])]
    #[AfterValidator([TestValidatorForOrder::class, 'formatName'])]
    public string $name;

    #[BeforeValidator([TestValidatorForOrder::class, 'addAttrBefore'])]
    #[AfterValidator([TestValidatorForOrder::class, 'addAttrAfter'])]
    public string $testProp;
}

class FieldValidationManagerOrderTest extends TestCase
{
    private ReflectionProperty $nameProperty;
    private ReflectionProperty $testPropProperty;
    private StringField $field;

    protected function setUp(): void
    {
        $class = new TestClassForOrder();
        $this->nameProperty = new ReflectionProperty($class, 'name');
        $this->testPropProperty = new ReflectionProperty($class, 'testProp');
        $this->field = new StringField();
    }

    /**
     * PropertyOperatorを使用したバリデーション順序のテスト
     * Before -> After の順で属性バリデーションが実行される
     */
    #[Test]
    public function testValidationOrder(): void
    {
        $manager = FieldValidationManager::createFromProperty($this->nameProperty, $this->field);
        $inputData = new InputData(['name' => 'john']);
        $operator = PropertyOperator::create($this->nameProperty, $inputData, $this->field);

        $result = $manager->processValidation($operator);

        $this->assertEquals('john', $operator->value->value);
        // Before(validateLength) -> After(formatName)
        $this->assertEquals('John', $result->value->value);
    }

    /**
     * 複数のFieldValidatorが正しい順序で適用されることを確認するテスト
     */
    #[Test]
    public function testMultipleFieldValidatorsOrder(): void
    {
        $firstBeforeValidator = new FieldValidator('name', 'before');
        $firstBeforeValidator->setValidator(fn(string $value) => $value . '_before1');

        $secondBeforeValidator = new FieldValidator('name', 'before');
        $secondBeforeValidator->setValidator(fn(string $value) => $value . '_before2');

        $afterValidator = new FieldValidator('name', 'after');
        $afterValidator->setValidator(fn(string $value) => $value . '_after');

        // TestClassForOrderには属性があるが、このテストではFieldValidatorのみの順序を確認
        $manager = FieldValidationManager::createFromProperty(
            $this->nameProperty, // nameプロパティを使用
            $this->field,
            [$firstBeforeValidator, $secondBeforeValidator, $afterValidator],
        );

        $inputData = new InputData(['name' => 'test']);
        $original = PropertyOperator::create($this->nameProperty, $inputData, $this->field);

        $result = $manager->processValidation($original);

        // 実行順: attr_before -> field_before1 -> field_before2 -> field_after -> attr_after
        // attr_before: validateLength (変更なし)
        // field_before1: + '_before1'
        // field_before2: + '_before2'
        // field_after: + '_after'
        // attr_after: formatName (先頭大文字化)
        $this->assertEquals('Test_before1_before2_after', $result->value->value);
    }

    /**
     * 属性バリデーターとFieldValidatorが正しい順序で適用されることを確認するテスト
     */
    #[Test]
    public function testAttributeAndFieldValidatorOrder(): void
    {
        $beforeFieldValidator = new FieldValidator('testProp', 'before');
        $beforeFieldValidator->setValidator(fn(string $value) => $value . '_field_before');

        $afterFieldValidator = new FieldValidator('testProp', 'after');
        $afterFieldValidator->setValidator(fn(string $value) => $value . '_field_after');

        $manager = FieldValidationManager::createFromProperty(
            $this->testPropProperty,
            $this->field,
            [$beforeFieldValidator, $afterFieldValidator],
        );

        $inputData = new InputData(['testProp' => 'base']);
        $original = PropertyOperator::create($this->testPropProperty, $inputData, $this->field);

        $result = $manager->processValidation($original);

        // 実行順: field_before -> attr_before -> attr_after -> field_after
        // field_before: + '_field_before'
        // attr_before: addAttrBefore (+ '_attr_before')
        // attr_after: addAttrAfter (+ '_attr_after')
        // field_after: + '_field_after'
        $this->assertEquals('base_field_before_attr_before_attr_after_field_after', $result->value->value);
    }
}
