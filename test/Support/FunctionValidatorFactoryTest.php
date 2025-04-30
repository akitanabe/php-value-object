<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Support\FieldValidatorStorage;
use PhpValueObject\Support\FunctionValidatorFactory;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\WrapValidator;
use PhpValueObject\Validators\PlainValidator;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Validators\FunctionalValidatorMode;
use ReflectionClass;
use ReflectionProperty;

/**
 * テスト用のクラス
 */
class TestModelWithValidators
{
    #[BeforeValidator([TestModelWithValidators::class, 'validateBefore'])]
    #[AfterValidator([TestModelWithValidators::class, 'validateAfter'])]
    #[WrapValidator([TestModelWithValidators::class, 'validateWrap'])]
    #[PlainValidator([TestModelWithValidators::class, 'validatePlain'])]
    public string $testProperty = '';

    public static function validateBefore(string $value): string
    {
        return 'before_' . $value;
    }

    public static function validateAfter(string $value): string
    {
        return $value . '_after';
    }

    public static function validateWrap(string $value, callable $next): string
    {
        return 'wrap_' . $next($value) . '_wrap';
    }

    public static function validatePlain(string $value): string
    {
        return 'plain_' . $value;
    }
}

class FunctionValidatorFactoryTest extends TestCase
{
    private FieldValidatorStorage $validatorStorage;
    private ReflectionProperty $testProperty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validatorStorage = new FieldValidatorStorage();

        // TestModelWithValidatorsのtestPropertyを取得
        $reflectionClass = new ReflectionClass(TestModelWithValidators::class);
        $this->testProperty = $reflectionClass->getProperty('testProperty');

        // FieldValidatorStorageにテスト用のバリデータを登録
        $beforeValidator = new FieldValidator('testProperty', FunctionalValidatorMode::BEFORE);
        $beforeValidator->setCallable(function ($value) {
            return 'field_before_' . $value;
        });

        $afterValidator = new FieldValidator('testProperty', FunctionalValidatorMode::AFTER);
        $afterValidator->setCallable(function ($value) {
            return $value . '_field_after';
        });

        $this->validatorStorage->addValidator($this->testProperty, $beforeValidator);
        $this->validatorStorage->addValidator($this->testProperty, $afterValidator);
    }

    #[Test]
    public function createFromStorage_ReturnsFactoryWithCorrectValidators(): void
    {
        // Act
        $factory = FunctionValidatorFactory::createFromStorage($this->validatorStorage, $this->testProperty);

        // Assert
        $this->assertInstanceOf(FunctionValidatorFactory::class, $factory);

        // createValidators()の結果を検証
        $validators = $factory->createValidators();
        $this->assertCount(6, $validators);

        // FieldValidatorStorageからのバリデータ
        $this->assertInstanceOf(FunctionBeforeValidator::class, $validators[0]);
        $this->assertInstanceOf(FunctionAfterValidator::class, $validators[1]);

        // アトリビュートからのバリデータ
        $this->assertInstanceOf(FunctionBeforeValidator::class, $validators[2]);
        $this->assertInstanceOf(FunctionAfterValidator::class, $validators[3]);
        $this->assertInstanceOf(FunctionWrapValidator::class, $validators[4]);
        $this->assertInstanceOf(FunctionPlainValidator::class, $validators[5]);
    }
}
