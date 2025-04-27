<?php

declare(strict_types=1);

namespace Test\Support;

use InvalidArgumentException;
use PhpValueObject\Support\FieldValidatorFactory;
use PhpValueObject\Validators\FieldValidator;
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
            #[FieldValidator(field: 'name', mode: 'after')]
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
        $validator = $validators[0];

        // バリデータのプロパティを確認
        $this->assertInstanceOf(FieldValidator::class, $validator);
        $this->assertSame('name', $validator->field);
        // mode は private なので直接アクセスできないが、validate メソッドの挙動で間接的に確認可能
        // validator callable が正しく設定されているか（リフレクション等で確認も可能だが、ここでは省略）
    }

    #[Test]
    public function testCreateFromClassWithMultipleValidatorsForSingleField(): void
    {
        // 1つのフィールドに複数のバリデータを持つクラス
        $class = new class {
            #[FieldValidator(field: 'age', mode: 'before')]
            public static function validateAgeType(mixed $value): int
            {
                return (int) $value;
            }

            #[FieldValidator(field: 'age', mode: 'after')]
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
        $this->assertInstanceOf(FieldValidator::class, $validators[0]);
        $this->assertInstanceOf(FieldValidator::class, $validators[1]);
        $this->assertSame('age', $validators[0]->field);
        $this->assertSame('age', $validators[1]->field);
    }

    #[Test]
    public function testCreateFromClassWithMultipleFields(): void
    {
        // 複数のフィールドにバリデータを持つクラス
        $class = new class {
            #[FieldValidator(field: 'email', mode: 'plain')]
            public static function validateEmailFormat(string $value): string
            {
                // dummy validation
                return $value;
            }

            #[FieldValidator(field: 'password', mode: 'wrap')]
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
        $this->assertSame('email', $emailValidators[0]->field);

        // 'password' フィールドのバリデータを取得
        $passwordValidators = $factory->getValidatorsForField('password');
        $this->assertCount(1, $passwordValidators);
        $this->assertSame('password', $passwordValidators[0]->field);

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
     * @param string $mode テスト対象のモード
     */
    #[DataProvider('modeProvider')]
    #[Test]
    public function testCreateFromClassWithDifferentModes(string $mode): void
    {
        // 動的にモードを設定するクラス定義 (クロージャを使用)
        $classDefinition = <<<PHP
        return new class ('{$mode}') {
            private string \$mode;
            public function __construct(string \$mode) { \$this->mode = \$mode; }

            #[\\PhpValueObject\\Validators\\FieldValidator(field: 'data', mode: '{$mode}')]
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
        $validator = $validators[0];

        // バリデータのプロパティを確認
        $this->assertInstanceOf(FieldValidator::class, $validator);
        $this->assertSame('data', $validator->field);
        // mode の検証は FieldValidator 自身のテストで行われるべきだが、
        // ここでは異なるモードでファクトリがエラーなく生成できることを確認
    }

    /**
     * @return array<string, array{string}>
     */
    public static function modeProvider(): array
    {
        return [
            'plain mode' => ['plain'],
            'wrap mode' => ['wrap'],
            'before mode' => ['before'],
            'after mode' => ['after'],
        ];
    }
}
