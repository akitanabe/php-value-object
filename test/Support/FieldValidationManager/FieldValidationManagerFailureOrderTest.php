<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\FunctionValidatorFactory;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\BeforeValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Validators\FunctionalValidator;
use ReflectionAttribute;

// テスト用のバリデータクラス
class TestValidatorForFailureOrder
{
    public static function failIfFail1(string $value): string
    {
        if ($value === 'fail1') {
            throw new ValidationException('最初のバリデーションに失敗');
        }
        return $value;
    }

    public static function failIfFail2(string $value): string
    {
        if ($value === 'fail2') {
            throw new ValidationException('2番目のバリデーションに失敗');
        }
        return $value;
    }

    // 常に成功するバリデータ
    public static function alwaysPass(string $value): string
    {
        return $value;
    }
}

// テスト対象の属性を持つクラス
class TestClassForFailureOrder1
{
    #[BeforeValidator([TestValidatorForFailureOrder::class, 'failIfFail1'])]
    public string $testProp;
}

class TestClassForFailureOrder2
{
    #[BeforeValidator([TestValidatorForFailureOrder::class, 'alwaysPass'])] // これは成功する
    #[AfterValidator([TestValidatorForFailureOrder::class, 'failIfFail2'])] // これで失敗する
    public string $testProp;
}


class FieldValidationManagerFailureOrderTest extends TestCase
{
    private StringField $field;

    protected function setUp(): void
    {
        $this->field = new StringField();
    }

    /**
     * 複合的なバリデーション失敗時に最初に失敗したバリデーターのエラーが発生することを確認するテスト
     */
    #[Test]
    public function testComplexValidationFailureOrder(): void
    {
        $testClass = new TestClassForFailureOrder1();
        $prop = new ReflectionProperty($testClass, 'testProp');

        // 属性バリデータを取得して FunctionValidatorFactory を作成
        $functionValidators = AttributeHelper::getAttributeInstances(
            $prop,
            FunctionalValidator::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );
        $functionValidatorFactory = new FunctionValidatorFactory([], $functionValidators);

        // FunctionValidatorFactory を使用してマネージャーを作成
        $manager = FieldValidationManager::createFromProperty($prop, $this->field, $functionValidatorFactory);

        $inputData = new InputData(['testProp' => 'fail1']);
        $operator = PropertyOperator::create($prop, $inputData, $this->field);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('最初のバリデーションに失敗');
        $manager->processValidation($operator);
    }

    /**
     * 複合的なバリデーション失敗で2番目のバリデーターのエラーが発生することを確認するテスト
     */
    #[Test]
    public function testComplexValidationSecondFailure(): void
    {
        $testClass = new TestClassForFailureOrder2();
        $prop = new ReflectionProperty($testClass, 'testProp');

        // 属性バリデータを取得して FunctionValidatorFactory を作成
        $functionValidators = AttributeHelper::getAttributeInstances(
            $prop,
            FunctionalValidator::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );
        $functionValidatorFactory = new FunctionValidatorFactory([], $functionValidators);

        // FunctionValidatorFactory を使用してマネージャーを作成
        $manager = FieldValidationManager::createFromProperty($prop, $this->field, $functionValidatorFactory);

        // 2番目のバリデーター(After)で失敗するケース
        $inputData = new InputData(['testProp' => 'fail2']);
        $operator = PropertyOperator::create($prop, $inputData, $this->field);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('2番目のバリデーションに失敗');
        $manager->processValidation($operator);
    }
}
