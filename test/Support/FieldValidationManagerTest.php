<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Enums\ValidatorMode;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\InputData;

class TestClass
{
    #[BeforeValidator([TestValidator::class, 'validateLength'])]
    #[AfterValidator([TestValidator::class, 'formatName'])]
    public string $name;
}

class FieldValidationManagerTest extends TestCase
{
    private FieldValidationManager $managerWithAttributes;
    private FieldValidationManager $managerWithFieldValidators;
    private FieldValidationManager $managerWithBoth;
    private ReflectionProperty $property;

    protected function setUp(): void
    {
        $class = new TestClass();
        $this->property = new ReflectionProperty($class, 'name');

        $field = new StringField();

        // 属性のみを使用したマネージャー
        $this->managerWithAttributes = FieldValidationManager::createFromProperty($this->property, $field);

        // FieldValidatorのみを使用したマネージャー
        $beforeValidator = new FieldValidator('name', ValidatorMode::BEFORE);
        $beforeValidator->setValidator(fn(string $value) => TestValidator::validateLength($value));
        $afterValidator = new FieldValidator('name', ValidatorMode::AFTER);
        $afterValidator->setValidator(fn(string $value) => TestValidator::formatName($value));
        $this->managerWithFieldValidators = FieldValidationManager::createFromProperty(
            $this->property,
            $field,
            [$beforeValidator, $afterValidator],
        );

        // 属性とFieldValidatorを組み合わせたマネージャー
        $additionalBeforeValidator = new FieldValidator('name', ValidatorMode::BEFORE);
        $additionalBeforeValidator->setValidator(
            fn(string $value) => strlen($value) > 5 ? $value : throw new ValidationException(
                '6文字以上必要です',
            ),
        );
        $this->managerWithBoth = FieldValidationManager::createFromProperty(
            $this->property,
            $field,
            [$additionalBeforeValidator],
        );
    }

    /**
     * PropertyOperatorを使用したバリデーション失敗のテスト
     * 3文字未満の入力を検証した場合、ValidationExceptionが発生する
     */
    public function testValidationThrowsException(): void
    {
        $inputData = new InputData(['name' => 'ab']);
        $operator = PropertyOperator::create($this->property, $inputData, new StringField());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithAttributes->processValidation($operator);
    }

    /**
     * PropertyOperatorを使用したバリデーション成功のテスト
     * 値が変更された場合は新しいPropertyOperatorが返される
     */
    public function testValidationSuccess(): void
    {
        // テスト用のPropertyOperatorを作成
        $inputData = new InputData(['name' => 'abc']);
        $original = PropertyOperator::create($this->property, $inputData, new StringField());

        $result = $this->managerWithAttributes->processValidation($original);

        // 新しいインスタンスが返されることを確認
        $this->assertNotSame($original, $result);
        // 元のオブジェクトの値は変更されていない
        $this->assertEquals('abc', $original->value);
        // 新しいオブジェクトの値は変更されている
        $this->assertEquals('Abc', $result->value);
        // クラス名とプロパティ名は維持されている
        $this->assertEquals($original->class, $result->class);
        $this->assertEquals($original->name, $result->name);
    }

    /**
     * PropertyOperatorを使用したバリデーション順序のテスト
     * Before -> After の順で属性バリデーションが実行される
     */
    public function testValidationOrder(): void
    {
        $inputData = new InputData(['name' => 'john']);
        $operator = PropertyOperator::create($this->property, $inputData, new StringField());

        $result = $this->managerWithAttributes->processValidation($operator);

        // 元のオブジェクトの値は変更されていない
        $this->assertEquals('john', $operator->value);
        // 新しいオブジェクトの値は変更されている（最初の文字が大文字に）
        $this->assertEquals('John', $result->value);
    }

    /**
     * PropertyOperatorを使用したFieldValidatorのバリデーション失敗テスト
     * 入力値の長さが3文字未満の場合、ValidationExceptionが発生する
     */
    public function testFieldValidation(): void
    {
        $inputData = new InputData(['name' => 'ab']);
        $operator = PropertyOperator::create($this->property, $inputData, new StringField());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithFieldValidators->processValidation($operator);
    }

    /**
     * PropertyOperatorを使用したFieldValidatorのバリデーション成功テスト
     * 値が変更された場合は新しいPropertyOperatorが返される
     */
    public function testFieldValidationSuccess(): void
    {
        $inputData = new InputData(['name' => 'john']);
        $original = PropertyOperator::create($this->property, $inputData, new StringField());

        $result = $this->managerWithFieldValidators->processValidation($original);

        // 新しいインスタンスが返されることを確認
        $this->assertNotSame($original, $result);
        // 元のオブジェクトの値は変更されていない
        $this->assertEquals('john', $original->value);
        // 新しいオブジェクトの値は変更されている
        $this->assertEquals('John', $result->value);
        // クラス名とプロパティ名は維持されている
        $this->assertEquals($original->class, $result->class);
        $this->assertEquals($original->name, $result->name);
    }

    /**
     * PropertyOperatorを使用した属性とFieldValidatorの組み合わせテスト
     * BeforeValidator属性とFieldValidatorが両方適用される
     * 最初のバリデーション（3文字以上）は通過するが、2番目のバリデーション（6文字以上）で失敗
     */
    public function testCombinedValidation(): void
    {
        $inputData = new InputData(['name' => 'abcde']);
        $operator = PropertyOperator::create($this->property, $inputData, new StringField());

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('6文字以上必要です');
        $this->managerWithBoth->processValidation($operator);
    }

    /**
     * PropertyOperatorを使用した属性とFieldValidatorの組み合わせテスト（成功ケース）
     * 全てのバリデーションを通過し、新しいPropertyOperatorが返される
     */
    public function testCombinedValidationSuccess(): void
    {
        $inputData = new InputData(['name' => 'abcdef']);
        $original = PropertyOperator::create($this->property, $inputData, new StringField());

        $result = $this->managerWithBoth->processValidation($original);

        // 新しいインスタンスが返されることを確認
        $this->assertNotSame($original, $result);
        // 元のオブジェクトの値は変更されていない
        $this->assertEquals('abcdef', $original->value);
        // 新しいオブジェクトの値は変更されている
        $this->assertEquals('Abcdef', $result->value);
        // クラス名とプロパティ名は維持されている
        $this->assertEquals($original->class, $result->class);
        $this->assertEquals($original->name, $result->name);
    }
}
