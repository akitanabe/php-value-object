<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use InvalidArgumentException;
use PhpValueObject\Support\FieldValidatorFactory;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Core\Validators\FunctionValidator;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Validators\ValidatorMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Exception;

final class FieldValidatorFactoryTest extends TestCase
{
    #[Test]
    public function testCreateFromClassWithNoValidators(): void
    {
        // バリデータメソッドを持たないクラス
        $class = new class {};
        $refClass = new ReflectionClass($class);

        // ファクトリを生成
        $factory = FieldValidatorFactory::createFromClass($refClass);

        // 特定のフィールドに対するバリデータがないことを確認
        $this->assertEmpty($factory->getValidatorsForField('anyField'));
    }

    #[Test]
    public function testCreateFromClassWithSingleValidator(): void
    {
        // 1つのフィールドに1つのバリデータを持つクラス
        $class = new class {
            #[FieldValidator(field: 'name', mode: ValidatorMode::AFTER)]
            public static function validateName(string $value): string
            {
                return $value . '_validated';
            }
        };
        $refClass = new ReflectionClass($class);

        // ファクトリを生成
        $factory = FieldValidatorFactory::createFromClass($refClass);

        // 'name' フィールドのバリデータを取得
        $validators = $factory->getValidatorsForField('name');

        // バリデータが1つ存在することを確認
        $this->assertCount(1, $validators);
        $functionValidator = $validators[0];

        // バリデータの型を確認 (mode: 'after' なので AfterValidator)
        $this->assertInstanceOf(FunctionAfterValidator::class, $functionValidator);
        // FunctionValidator には field プロパティはない
    }

    #[Test]
    public function testCreateFromClassWithMultipleValidatorsForSingleField(): void
    {
        // 1つのフィールドに複数のバリデータを持つクラス
        $class = new class {
            #[FieldValidator(field: 'age', mode: ValidatorMode::BEFORE)]
            public static function validateAgeType(mixed $value): int
            {
                return (int) $value;
            }

            #[FieldValidator(field: 'age', mode: ValidatorMode::AFTER)]
            public static function validateAgeRange(int $value): int
            {
                if ($value < 0) {
                    throw new Exception('Age cannot be negative');
                }
                return $value;
            }
        };
        $refClass = new ReflectionClass($class);

        // ファクトリを生成
        $factory = FieldValidatorFactory::createFromClass($refClass);

        // 'age' フィールドのバリデータを取得
        $validators = $factory->getValidatorsForField('age');

        // バリデータが2つ存在することを確認
        $this->assertCount(2, $validators);
        // モードに応じた正しい FunctionValidator 型か確認
        $this->assertInstanceOf(FunctionBeforeValidator::class, $validators[0]); // mode: 'before'
        $this->assertInstanceOf(FunctionAfterValidator::class, $validators[1]); // mode: 'after'
        // FunctionValidator には field プロパティはない
    }

    #[Test]
    public function testCreateFromClassWithMultipleFields(): void
    {
        // 複数のフィールドにバリデータを持つクラス
        $class = new class {
            #[FieldValidator(field: 'email', mode: ValidatorMode::PLAIN)]
            public static function validateEmailFormat(string $value): string
            {
                // dummy validation
                return $value;
            }

            #[FieldValidator(field: 'password', mode: ValidatorMode::WRAP)]
            public static function hashPassword(string $value, callable $next): string
            {
                // dummy validation
                return 'hashed_' . $next($value);
            }
        };
        $refClass = new ReflectionClass($class);

        // ファクトリを生成
        $factory = FieldValidatorFactory::createFromClass($refClass);

        // 'email' フィールドのバリデータを取得
        $emailValidators = $factory->getValidatorsForField('email');
        $this->assertCount(1, $emailValidators);
        $this->assertInstanceOf(FunctionPlainValidator::class, $emailValidators[0]); // mode: 'plain'

        // 'password' フィールドのバリデータを取得
        $passwordValidators = $factory->getValidatorsForField('password');
        $this->assertCount(1, $passwordValidators);
        $this->assertInstanceOf(FunctionWrapValidator::class, $passwordValidators[0]); // mode: 'wrap'

        // 存在しないフィールドのバリデータを取得
        $this->assertEmpty($factory->getValidatorsForField('nonExistentField'));
    }

    #[Test]
    public function testThrowsExceptionForNonStaticMethod(): void
    {
        // 非静的メソッドに FieldValidator 属性を持つクラス
        $class = new class {
            #[FieldValidator(field: 'invalid')]
            public function nonStaticValidator(string $value): string // static でない
            {
                return $value;
            }
        };
        $refClass = new ReflectionClass($class);

        // 例外がスローされることを期待
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches(
            '/Method .*::nonStaticValidator must be static for use with FieldValidator/',
        );

        // ファクトリ生成を試みる
        FieldValidatorFactory::createFromClass($refClass);
    }

    /**
     * @param ValidatorMode $mode テスト対象のモード
     * @param class-string<FunctionValidator> $expectedClass 期待される FunctionValidator のクラス名
     */
    #[DataProvider('modeProvider')]
    #[Test]
    public function testCreateFromClassWithDifferentModes(ValidatorMode $mode, string $expectedClass): void
    {
        // 動的にモードを設定するクラス定義 (クロージャを使用)
        // FieldValidator の namespace を修正
        $modeString = $mode->name;
        $classDefinition = <<<PHP
        return new class ('{$modeString}') {
            private string \$mode;
            public function __construct(string \$mode) { \$this->mode = \$mode; }

            #[\\PhpValueObject\\Validators\\FieldValidator(field: 'data', mode: \\PhpValueObject\\Validators\\ValidatorMode::{$modeString})]
            public static function validateData(mixed \$value): mixed
            {
                return \$value;
            }
        };
        PHP;
        $class = eval($classDefinition);

        $refClass = new ReflectionClass($class);

        // ファクトリを生成
        $factory = FieldValidatorFactory::createFromClass($refClass);

        // 'data' フィールドのバリデータを取得
        $validators = $factory->getValidatorsForField('data');

        // バリデータが1つ存在することを確認
        $this->assertCount(1, $validators);
        $functionValidator = $validators[0];

        // 正しい FunctionValidator 型が生成されているか確認
        $this->assertInstanceOf($expectedClass, $functionValidator);
        // FunctionValidator には field プロパティはない
    }

    /**
     * @return array<string, array{ValidatorMode, class-string<FunctionValidator>}>
     */
    public static function modeProvider(): array
    {
        return [
            'plain mode' => [ValidatorMode::PLAIN, FunctionPlainValidator::class],
            'wrap mode' => [ValidatorMode::WRAP, FunctionWrapValidator::class],
            'before mode' => [ValidatorMode::BEFORE, FunctionBeforeValidator::class],
            'after mode' => [ValidatorMode::AFTER, FunctionAfterValidator::class],
        ];
    }
}
