<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support\FieldValidationManager;

use PhpValueObject\Fields\StringField;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Support\FunctionValidatorFactory; // FieldValidatorFactoryから変更
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\SystemValidatorFactory;
use PhpValueObject\Validators\AfterValidator; // Used for property attribute
use PhpValueObject\Validators\BeforeValidator; // Used for property attribute
use PhpValueObject\Validators\FieldValidator; // Used only for attribute reading/test setup
// Added
use PhpValueObject\Core\Validators\Validatorable;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\ValidatorMode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
// 追加
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
    private SystemValidatorFactory $systemValidatorFactory; // SystemValidatorFactoryのインスタンス

    // テスト用の Pre System Validator
    private Validatorable $preSystemValidator;
    // テスト用の Standard System Validator
    private Validatorable $standardSystemValidator;

    protected function setUp(): void
    {
        $class = new TestClassForOrder();
        $this->nameProperty = new ReflectionProperty($class, 'name');
        $this->testPropProperty = new ReflectionProperty($class, 'testProp');
        $this->field = new StringField();

        // テスト用のシステムバリデータを作成
        $this->preSystemValidator = new class implements Validatorable {
            public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
            {
                // 処理を行い、次のハンドラーを呼び出す
                $processedValue = $value . '_preSys';
                return $handler ? ($handler)($processedValue) : $processedValue;
            }
        };
        $this->standardSystemValidator = new class implements Validatorable {
            public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
            {
                // 処理を行い、次のハンドラーを呼び出す (このバリデータは最後なのでハンドラ呼び出しは不要だが、念のため)
                $processedValue = $value . '_stdSys';
                // 最後のバリデータは通常ハンドラを呼び出さないため、そのまま値を返す
                return $processedValue;
            }
        };

        // テスト用のSystemValidatorFactoryを作成
        $this->systemValidatorFactory = new SystemValidatorFactory(
            [$this->preSystemValidator],
            [$this->standardSystemValidator],
        );
    }

    /**
     * PropertyOperatorを使用したバリデーション順序のテスト
     * Before -> After の順で属性バリデーションが実行される
     */
    #[Test]
    public function testValidationOrder(): void
    {
        $functionalValidators = FunctionValidatorFactory::getFunctionalValidators($this->nameProperty);
        $functionValidatorFactory = new FunctionValidatorFactory([], $functionalValidators);
        // SystemValidatorFactory を渡して Manager を作成
        $manager = FieldValidationManager::createFromProperty(
            $this->nameProperty,
            $this->field,
            functionValidatorFactory: $functionValidatorFactory, // 生成したファクトリを渡す
            systemValidators: $this->systemValidatorFactory, // SystemValidator を渡す
        );
        $inputData = new InputData(['name' => 'john']);
        $operator = PropertyOperator::create($this->nameProperty, $inputData, $this->field);

        $result = $manager->processValidation($operator);

        $this->assertEquals('john', $operator->value->value);
        // 実行順: preSys -> attr_before(validateLength) -> attr_after(formatName) -> stdSys
        // preSys: + '_preSys'
        // attr_before: validateLength (変更なし)
        // attr_after: formatName (先頭大文字化)
        // stdSys: + '_stdSys'
        $this->assertEquals('John_preSys_stdSys', $result->value->value);
    }

    /**
     * 複数のFieldValidatorが正しい順序で適用されることを確認するテスト
     */
    #[Test]
    public function testMultipleFieldValidatorsOrder(): void
    {
        $firstBeforeValidator = new FieldValidator('name', ValidatorMode::BEFORE);
        $secondBeforeValidator = new FieldValidator('name', ValidatorMode::BEFORE);
        $afterValidator = new FieldValidator('name', ValidatorMode::AFTER);

        // リフレクションを使用して FunctionValidatorFactory インスタンスを生成・設定
        $beforeFunc1 = fn(string $value) => $value . '_before1';
        $beforeFunc2 = fn(string $value) => $value . '_before2';
        $afterFunc = fn(string $value) => $value . '_after';

        $fieldValidators = [
            $firstBeforeValidator->setCallable($beforeFunc1),
            $secondBeforeValidator->setCallable($beforeFunc2),
            $afterValidator->setCallable($afterFunc),
        ];

        $functionalValidators = FunctionValidatorFactory::getFunctionalValidators($this->nameProperty);
        $functionValidatorFactory = new FunctionValidatorFactory($fieldValidators, $functionalValidators);

        // SystemValidatorFactory と FunctionValidatorFactory を渡して Manager を作成
        $manager = FieldValidationManager::createFromProperty(
            property: $this->nameProperty, // nameプロパティを使用
            field: $this->field,
            functionValidatorFactory: $functionValidatorFactory, // 生成したファクトリを渡す
            systemValidators: $this->systemValidatorFactory, // SystemValidator を渡す
        );

        $inputData = new InputData(['name' => 'test']);
        $original = PropertyOperator::create($this->nameProperty, $inputData, $this->field);

        $result = $manager->processValidation($original);

        // 実行順: preSys -> field_before1 -> field_before2 -> attr_before -> attr_after -> field_after -> stdSys
        // preSys: + '_preSys'
        // field_before1: + '_before1'
        // field_before2: + '_before2'
        // attr_before: validateLength (変更なし)
        // attr_after: formatName (先頭大文字化)
        // field_after: + '_after'
        // stdSys: + '_stdSys'
        // 修正: stdSys は after より前に実行される
        $this->assertEquals('Test_preSys_before1_before2_stdSys_after', $result->value->value);
    }

    /**
     * 属性バリデーターとFieldValidatorが正しい順序で適用されることを確認するテスト
     */
    #[Test]
    public function testAttributeAndFieldValidatorOrder(): void
    {
        $beforeFieldValidator = new FieldValidator('testProp', ValidatorMode::BEFORE);
        $afterFieldValidator = new FieldValidator('testProp', ValidatorMode::AFTER);

        $beforeFunc = fn(string $value) => $value . '_field_before';
        $afterFunc = fn(string $value) => $value . '_field_after';

        $fieldValidators = [
            $beforeFieldValidator->setCallable($beforeFunc),
            $afterFieldValidator->setCallable($afterFunc),
        ];

        $functionalValidators = FunctionValidatorFactory::getFunctionalValidators($this->testPropProperty);
        $functionValidatorFactory = new FunctionValidatorFactory($fieldValidators, $functionalValidators);

        // SystemValidatorFactory と FunctionValidatorFactory を渡して Manager を作成
        $manager = FieldValidationManager::createFromProperty(
            property: $this->testPropProperty,
            field: $this->field,
            functionValidatorFactory: $functionValidatorFactory, // 生成したファクトリを渡す
            systemValidators: $this->systemValidatorFactory, // SystemValidator を渡す
        );

        $inputData = new InputData(['testProp' => 'base']);
        $original = PropertyOperator::create($this->testPropProperty, $inputData, $this->field);

        $result = $manager->processValidation($original);

        // 実行順: preSys -> field_before -> attr_before -> attr_after -> field_after -> stdSys
        // preSys: + '_preSys'
        // field_before: + '_field_before'
        // attr_before: addAttrBefore (+ '_attr_before')
        // attr_after: addAttrAfter (+ '_attr_after')
        // field_after: + '_field_after'
        // stdSys: + '_stdSys'
        // 修正: stdSys は after より前に実行される
        $this->assertEquals(
            'base_preSys_field_before_attr_before_stdSys_attr_after_field_after',
            $result->value->value,
        );
    }
}
