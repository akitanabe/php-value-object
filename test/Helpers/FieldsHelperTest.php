<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Helpers;

use InvalidArgumentException;
use PhpValueObject\BaseModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Test\Support\TestValidator;
use PhpValueObject\Validators\FieldValidator;
use DateTime;
use ReflectionClass;

class DateTimeFactory
{
    public function __invoke(string $value): DateTime
    {
        return new DateTime($value);
    }

    public static function create(string $value): DateTime
    {
        return new DateTime($value);
    }
}

class EmptyValidatorModel extends BaseModel
{
    public string $name;
}

class SingleValidatorModel extends BaseModel
{
    public string $name;

    #[FieldValidator('name')]
    public function validateName(string $value): string
    {
        return TestValidator::validateLength($value);
    }
}

class MultipleValidatorModel extends BaseModel
{
    public string $name;
    public string $title;

    #[FieldValidator('name')]
    public function validateName(string $value): string
    {
        return TestValidator::validateLength($value);
    }

    #[FieldValidator('title')]
    public function validateTitle(string $value): string
    {
        return TestValidator::formatName($value);
    }
}

class FieldsHelperTest extends TestCase
{
    #[Test]
    public function identity(): void
    {
        $result = FieldsHelper::identity('test');

        $this->assertEquals('test', $result);
    }

    #[Test]
    public function createFactoryWithCustomFunction(): void
    {
        $customFactory = fn($value): string => strtoupper($value);
        $factoryFn = FieldsHelper::createFactory($customFactory);

        $result = $factoryFn('test');

        $this->assertEquals($customFactory('test'), $result);
    }

    #[Test]
    public function createFactoryWithCallableString(): void
    {
        $factoryFn = FieldsHelper::createFactory('strtolower');

        $result = $factoryFn('TEST');

        $this->assertEquals(strtolower('test'), $result);
    }

    #[Test]
    public function factoryWithInvalidCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore argument.type
        $factoryFn = FieldsHelper::createFactory('not_a_callable');
    }

    #[Test]
    public function factoryWithClass(): void
    {
        $factoryFn = FieldsHelper::createFactory(DateTimeFactory::class);

        $result = $factoryFn('2021-01-01');

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2021-01-01', $result->format('Y-m-d'));
    }

    #[Test]
    public function factoryWithCallableArray(): void
    {
        $factoryFn = FieldsHelper::createFactory([DateTimeFactory::class, 'create']);

        $result = $factoryFn('2022-01-01');

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2022-01-01', $result->format('Y-m-d'));
    }

    /**
     * バリデータが存在しない場合、空配列が返されることを確認
     */
    #[Test]
    public function getFieldValidatorsWithNoValidators(): void
    {
        $model = EmptyValidatorModel::fromArray(['name' => 'test']);
        $refClass = new ReflectionClass($model);

        $result = FieldsHelper::getFieldValidators($refClass, $model);

        $this->assertEmpty($result);
    }

    /**
     * 単一のFieldValidatorが存在する場合：
     * - 1つのFieldValidatorを含む配列が返される
     * - FieldValidatorのfieldプロパティが正しく設定されている
     * - FieldValidatorのvalidatorが正しく設定されている
     */
    #[Test]
    public function getFieldValidatorsWithSingleValidator(): void
    {
        $model = SingleValidatorModel::fromArray(['name' => 'test_name']);
        $refClass = new ReflectionClass($model);

        $result = FieldsHelper::getFieldValidators($refClass, $model);

        $this->assertCount(1, $result);
        $this->assertEquals('name', $result[0]->field);

        // バリデータが正しく機能することを確認
        $validValue = 'test_name';
        $this->assertEquals($validValue, $result[0]->validate($validValue));
    }

    /**
     * 複数のFieldValidatorが存在する場合：
     * - 複数のFieldValidatorを含む配列が返される
     * - 各FieldValidatorのfieldプロパティが正しく設定されている
     * - 各FieldValidatorのvalidatorが正しく設定されている
     */
    #[Test]
    public function getFieldValidatorsWithMultipleValidators(): void
    {
        $model = MultipleValidatorModel::fromArray(['name' => 'test_name', 'title' => '']);
        $refClass = new ReflectionClass($model);

        $result = FieldsHelper::getFieldValidators($refClass, $model);

        $filter = fn(string $field): array => array_values(array_filter($result, fn($v) => $v->field === $field));
        $this->assertCount(2, $result);


        // nameフィールドのバリデータをテスト
        $nameValidator = $filter('name')[0];
        $this->assertEquals('name', $nameValidator->field);
        $validNameValue = 'test_name';
        $this->assertEquals($validNameValue, $nameValidator->validate($validNameValue));

        // titleフィールドのバリデータをテスト
        $titleValidator = $filter('title')[0];
        $this->assertEquals('title', $titleValidator->field);
        $this->assertEquals('Test', $titleValidator->validate('test'));
    }
}
