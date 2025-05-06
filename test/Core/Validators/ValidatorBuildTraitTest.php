<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Core\Validators\Validatorable;
use PhpValueObject\Core\Validators\ValidatorBuildTrait;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use InvalidArgumentException;

/**
 * ValidatorBuildTraitのテスト
 */
class ValidatorBuildTraitTest extends TestCase
{
    /**
     * 型情報がある場合の正常系
     */
    #[Test]
    public function buildWithValidTypeHints(): void
    {
        // 準備
        $testDependency = new TestDependency();
        $definitions = new ValidatorDefinitions();
        $definitions->register($testDependency);

        // 実行
        $validator = TestValidator::build($definitions);

        // 検証
        $this->assertInstanceOf(TestValidator::class, $validator);
        $this->assertSame($testDependency, $validator->getDependency());
    }

    /**
     * デフォルト値がある場合の正常系
     */
    #[Test]
    public function buildWithDefaultValue(): void
    {
        // 準備
        $definitions = new ValidatorDefinitions();

        // 実行
        $validator = TestValidatorWithDefault::build($definitions);

        // 検証
        $this->assertInstanceOf(TestValidatorWithDefault::class, $validator);
        $this->assertSame('default', $validator->getDependency()->getValue());
    }

    /**
     * 依存関係もデフォルト値もない場合に例外が発生する
     */
    #[Test]
    public function buildWithMissingDependenciesThrowsException(): void
    {
        // 準備
        $definitions = new ValidatorDefinitions();

        // 期待値
        $this->expectException(InvalidArgumentException::class);

        // 実行
        TestValidator::build($definitions);
    }

    /**
     * パラメータに型情報がない場合に例外が発生する
     */
    #[Test]
    public function buildWithoutTypeHintThrowsException(): void
    {
        // 準備
        $definitions = new ValidatorDefinitions();

        // 期待値
        $this->expectException(InvalidArgumentException::class);

        // 実行
        TestValidatorWithoutTypeHint::build($definitions);
    }
}

/**
 * テスト用の依存オブジェクトクラス
 */
class TestDependency
{
    private string $value;

    public function __construct(string $value = 'test')
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

/**
 * デフォルト値を持たないバリデータクラス
 */
class TestValidator implements Validatorable
{
    use ValidatorBuildTrait;

    private TestDependency $dependency;

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        return $value;
    }

    public function getDependency(): TestDependency
    {
        return $this->dependency;
    }
}

/**
 * デフォルト値を持つバリデータクラス
 */
class TestValidatorWithDefault implements Validatorable
{
    use ValidatorBuildTrait;

    private TestDependency $dependency;

    public function __construct(TestDependency $dependency = null)
    {
        $this->dependency = $dependency ?? new TestDependency('default');
    }

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        return $value;
    }

    public function getDependency(): TestDependency
    {
        return $this->dependency;
    }
}

/**
 * 型情報のないパラメータを持つバリデータクラス
 */
class TestValidatorWithoutTypeHint implements Validatorable
{
    use ValidatorBuildTrait;


    // @phpstan-ignore missingType.parameter,constructor.unusedParameter
    public function __construct($dependency) {}

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        return $value;
    }
}
