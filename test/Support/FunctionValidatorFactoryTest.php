<?php

declare(strict_types=1);

namespace PhSculptis\Test\Support;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PhSculptis\Core\Definitions\FunctionValidatorDefinition;
use PhSculptis\Core\Validators\FunctionValidator;
use PhSculptis\Support\FieldValidatorStorage;
use PhSculptis\Support\FunctionValidatorFactory;
use PhSculptis\Validators\BeforeValidator;
use PhSculptis\Validators\AfterValidator;
use PhSculptis\Validators\FieldValidator;
use PhSculptis\Validators\ValidatorMode;
use ReflectionClass;

class FunctionValidatorFactoryTest extends TestCase
{
    /**
     * コンストラクタでFieldValidatorとFunctionalValidatorを設定できることをテスト
     */
    #[Test]
    public function canCreateWithValidators(): void
    {
        // Arrange
        $fieldValidators = [new FieldValidator('testField', ValidatorMode::BEFORE),];
        $functionalValidators = [
            new BeforeValidator(fn($value) => $value),
        ];

        // Act
        $factory = new FunctionValidatorFactory($fieldValidators, $functionalValidators);

        // Assert
        $validators = $factory->getValidators();
        $this->assertCount(2, $validators);
        $this->assertSame(FunctionValidator::class, $validators[0]);
        $this->assertSame(FunctionValidator::class, $validators[1]);
    }

    /**
     * 空のバリデータでファクトリを作成できることをテスト
     */
    #[Test]
    public function canCreateWithEmptyValidators(): void
    {
        // Act
        $factory = new FunctionValidatorFactory();

        // Assert
        $validators = $factory->getValidators();
        $this->assertCount(0, $validators);
    }

    /**
     * FieldValidatorStorageからFunctionValidatorFactoryを作成できることをテスト
     */
    #[Test]
    public function canCreateFromStorage(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassForFactory::class);
        $storage = FieldValidatorStorage::createFromClass($refClass);
        $property = $refClass->getProperty('name');

        // Act
        $factory = FunctionValidatorFactory::createFromStorage($storage, $property);

        // Assert
        $this->assertInstanceOf(FunctionValidatorFactory::class, $factory);
        $validators = $factory->getValidators();
        $this->assertGreaterThan(0, count($validators));
    }

    /**
     * プロパティからFunctionalValidatorを取得できることをテスト
     */
    #[Test]
    public function canGetFunctionalValidators(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassForFactory::class);
        $property = $refClass->getProperty('name');

        // Act
        $functionalValidators = FunctionValidatorFactory::getFunctionalValidators($property);

        // Assert
        $this->assertCount(2, $functionalValidators); // BeforeValidator and AfterValidator
        $this->assertInstanceOf(BeforeValidator::class, $functionalValidators[0]);
        $this->assertInstanceOf(AfterValidator::class, $functionalValidators[1]);
    }

    /**
     * FunctionalValidatorが設定されていないプロパティから空配列を取得できることをテスト
     */
    #[Test]
    public function returnsEmptyArrayWhenNoFunctionalValidators(): void
    {
        // Arrange
        $refClass = new ReflectionClass(TestClassForFactory::class);
        $property = $refClass->getProperty('age');

        // Act
        $functionalValidators = FunctionValidatorFactory::getFunctionalValidators($property);

        // Assert
        $this->assertCount(0, $functionalValidators);
    }

    /**
     * FunctionValidatorDefinitionを作成できることをテスト
     */
    #[Test]
    public function canCreateDefinition(): void
    {
        // Arrange
        $fieldValidators = [new FieldValidator('testField', ValidatorMode::BEFORE),];
        $functionalValidators = [
            new BeforeValidator(fn($value) => $value),
        ];
        $factory = new FunctionValidatorFactory($fieldValidators, $functionalValidators);

        // Act
        $definition = $factory->createDefinition();

        // Assert
        $this->assertInstanceOf(FunctionValidatorDefinition::class, $definition);
        $this->assertCount(2, $definition);
    }

    /**
     * 空のバリデータでもFunctionValidatorDefinitionを作成できることをテスト
     */
    #[Test]
    public function canCreateDefinitionWithEmptyValidators(): void
    {
        // Arrange
        $factory = new FunctionValidatorFactory();

        // Act
        $definition = $factory->createDefinition();

        // Assert
        $this->assertInstanceOf(FunctionValidatorDefinition::class, $definition);
        $this->assertCount(0, $definition);
    }

    /**
     * getValidatorsメソッドが正しい数のFunctionValidatorクラスを返すことをテスト
     */
    #[Test]
    public function getValidatorsReturnsCorrectCount(): void
    {
        // Arrange
        $fieldValidators = [
            new FieldValidator('field1', ValidatorMode::BEFORE),
            new FieldValidator('field2', ValidatorMode::AFTER),
        ];
        $functionalValidators = [
            new BeforeValidator(fn($value) => $value),
            new AfterValidator(fn($value) => $value),
            new BeforeValidator(fn($value) => $value),
        ];
        $factory = new FunctionValidatorFactory($fieldValidators, $functionalValidators);

        // Act
        $validators = $factory->getValidators();

        // Assert
        $this->assertCount(5, $validators); // 2 field + 3 functional = 5
        foreach ($validators as $validator) {
            $this->assertSame(FunctionValidator::class, $validator);
        }
    }
}

/**
 * テスト用のクラス
 */
class TestClassForFactory
{
    #[BeforeValidator([self::class, 'validateNameBefore'])]
    #[AfterValidator([self::class, 'validateNameAfter'])]
    public string $name;

    public int $age;

    #[FieldValidator('name')]
    public static function validateName(): bool
    {
        return true;
    }

    public static function validateNameBefore(): bool
    {
        return true;
    }

    public static function validateNameAfter(): bool
    {
        return true;
    }
}
