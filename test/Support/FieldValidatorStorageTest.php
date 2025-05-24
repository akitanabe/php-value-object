<?php

declare(strict_types=1);

namespace PhSculptis\Test\Support;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PhSculptis\Validators\FieldValidator;
use PhSculptis\Support\FieldValidatorStorage;
use PhSculptis\Validators\ValidatorMode;
use ReflectionClass;
use ReflectionProperty;

class FieldValidatorStorageTest extends TestCase
{
    /**
     * クラスからFieldValidatorStorageインスタンスを生成できることをテスト
     * - クラスからストレージを作成する
     * - nameプロパティに1つのバリデータが設定されていることを確認
     * - ageプロパティに2つのバリデータが設定されていることを確認
     */
    #[Test]
    public function canCreateFieldValidatorStorageFromClass(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassWithValidators::class);

        // Act
        $storage = FieldValidatorStorage::createFromClass($refClass);

        // Assert
        $this->assertInstanceOf(FieldValidatorStorage::class, $storage);

        // name プロパティのバリデータが正しく設定されていることを確認
        $nameProperty = $refClass->getProperty('name');
        $validators = $storage->getValidatorsForProperty($nameProperty);
        $this->assertCount(1, $validators);
        $this->assertInstanceOf(FieldValidator::class, $validators[0]);
        $this->assertSame('name', $validators[0]->field);

        // age プロパティのバリデータが正しく設定されていることを確認
        $ageProperty = $refClass->getProperty('age');
        $validators = $storage->getValidatorsForProperty($ageProperty);
        $this->assertCount(2, $validators);
        $this->assertInstanceOf(FieldValidator::class, $validators[0]);
        $this->assertInstanceOf(FieldValidator::class, $validators[1]);
        $this->assertSame('age', $validators[0]->field);
        $this->assertSame('age', $validators[1]->field);
    }

    /**
     * 非staticメソッドにFieldValidatorアトリビュートが付いている場合に例外が発生することをテスト
     * - staticでないメソッドにアトリビュートが設定されたクラスを使用
     * - createFromClassメソッド呼び出し時に例外が発生することを確認
     */
    #[Test]
    public function throwsExceptionWhenNonStaticMethodHasAttribute(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassWithNonStaticValidator::class);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Method PhSculptis\Test\Support\TestClassWithNonStaticValidator::validateName must be static for use with FieldValidator',
        );
        FieldValidatorStorage::createFromClass($refClass);
    }

    /**
     * 存在しないプロパティ名がFieldValidatorアトリビュートで指定された場合に例外が発生することをテスト
     * - 存在しないプロパティ名を参照しているクラスを使用
     * - createFromClassメソッド呼び出し時に例外が発生することを確認
     */
    #[Test]
    public function throwsExceptionWhenInvalidPropertyNameSpecified(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassWithInvalidPropertyName::class);

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "Property 'nonExistentProperty' referenced in PhSculptis\Test\Support\TestClassWithInvalidPropertyName::validateNonExistent does not exist",
        );
        FieldValidatorStorage::createFromClass($refClass);
    }

    /**
     * addValidatorメソッドでプロパティにバリデータを追加できることをテスト
     * - 既存のプロパティに新しいバリデータを追加
     * - バリデータが正しく追加されていることを確認
     */
    #[Test]
    public function canAddValidatorToProperty(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassWithValidators::class);
        $storage = FieldValidatorStorage::createFromClass($refClass);
        $nameProperty = $refClass->getProperty('name');
        $validator = new FieldValidator('name', ValidatorMode::BEFORE);
        $validator->setCallable([TestClassWithValidators::class, 'additionalValidator']);

        // Act
        $storage->addValidator($nameProperty, $validator);

        // Assert
        $validators = $storage->getValidatorsForProperty($nameProperty);
        $this->assertCount(2, $validators);
        $this->assertSame($validator, $validators[1]);
    }

    /**
     * 存在しないプロパティに対してgetValidatorsForPropertyを呼び出すと空配列が返されることをテスト
     * - ストレージに存在しないプロパティに対してバリデータを取得
     * - 空配列が返されることを確認
     */
    #[Test]
    public function returnsEmptyArrayForNonExistentProperty(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassWithValidators::class);
        $storage = FieldValidatorStorage::createFromClass($refClass);
        $nonExistentProperty = new ReflectionProperty(TestClassWithDifferentProperties::class, 'email');

        // Act
        $validators = $storage->getValidatorsForProperty($nonExistentProperty);

        // Assert
        $this->assertEmpty($validators);
    }
}

/**
 * テスト用クラス：バリデータを持つクラス
 */
class TestClassWithValidators
{
    public string $name;
    public int $age;

    #[FieldValidator('name')]
    public static function validateName(string $value): bool
    {
        return strlen($value) > 3;
    }

    #[FieldValidator('age')]
    public static function validateAgeMin(int $value): bool
    {
        return $value >= 18;
    }

    #[FieldValidator('age', ValidatorMode::BEFORE)]
    public static function validateAgeMax(int $value): bool
    {
        return $value <= 120;
    }

    public static function additionalValidator(string $value): bool
    {
        return $value !== 'invalid';
    }
}

/**
 * テスト用クラス：非staticメソッドにアトリビュートが付いているクラス
 */
class TestClassWithNonStaticValidator
{
    public string $name;

    #[FieldValidator('name')]
    public function validateName(string $value): bool
    {
        return strlen($value) > 3;
    }
}

/**
 * テスト用クラス：存在しないプロパティ名が指定されているクラス
 */
class TestClassWithInvalidPropertyName
{
    public string $name;

    #[FieldValidator('nonExistentProperty')]
    public static function validateNonExistent(string $value): bool
    {
        return true;
    }
}

/**
 * テスト用クラス：異なるプロパティを持つクラス
 */
class TestClassWithDifferentProperties
{
    public string $email;
}
