<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use LogicException;
use RuntimeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PhpValueObject\Core\Definitions\FunctionValidatorDefinition;
use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Core\Validators\FunctionValidator;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\WrapValidator;
use PhpValueObject\Validators\PlainValidator;

/**
 * FunctionValidatorのビルドメソッドのテストクラス
 *
 * FunctionValidatorのstaticメソッドbuildをテストする
 * 実際のオブジェクトを使用したテスト実装
 */
#[CoversClass(FunctionValidator::class)]
class FunctionValidatorTest extends TestCase
{
    private ValidatorDefinitions $definitions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->definitions = new ValidatorDefinitions();
    }

    /**
     * FunctionValidator::buildメソッドが、
     * BEFOREモードのバリデーターを正しく作成することを検証
     */
    #[Test]
    public function buildShouldCreateBeforeValidator(): void
    {
        // 実際のBeforeValidatorオブジェクトを作成
        $validator = new BeforeValidator(fn($value) => 'before_' . $value);

        // FunctionValidatorDefinitionを作成し登録
        $validatorQueue = new FunctionValidatorDefinition([$validator]);
        $this->definitions->register($validatorQueue);

        // テスト対象メソッドを実行
        $result = FunctionValidator::build($this->definitions);

        // 結果の検証
        $this->assertInstanceOf(FunctionBeforeValidator::class, $result);

        // 実際に機能するか検証（バリデーション関数が実行されることを確認）
        $output = $result->validate('test');
        $this->assertEquals('before_test', $output);
    }

    /**
     * FunctionValidator::buildメソッドが、
     * AFTERモードのバリデーターを正しく作成することを検証
     */
    #[Test]
    public function buildShouldCreateAfterValidator(): void
    {
        // 実際のAfterValidatorオブジェクトを作成
        $validator = new AfterValidator(fn($value) => $value . '_after');

        // FunctionValidatorDefinitionを作成し登録
        $validatorQueue = new FunctionValidatorDefinition([$validator]);
        $this->definitions->register($validatorQueue);

        // テスト対象メソッドを実行
        $result = FunctionValidator::build($this->definitions);

        // 結果の検証
        $this->assertInstanceOf(FunctionAfterValidator::class, $result);

        // 実際に機能するか検証（バリデーション関数が実行されることを確認）
        $output = $result->validate('test');
        $this->assertEquals('test_after', $output);
    }

    /**
     * FunctionValidator::buildメソッドが、
     * WRAPモードのバリデーターを正しく作成することを検証
     */
    #[Test]
    public function buildShouldCreateWrapValidator(): void
    {
        // 実際のWrapValidatorオブジェクトを作成
        $validator = new WrapValidator(function ($value, $handler) {
            return 'wrap_' . $handler($value) . '_wrap';
        });

        // FunctionValidatorDefinitionを作成し登録
        $validatorQueue = new FunctionValidatorDefinition([$validator]);
        $this->definitions->register($validatorQueue);

        // テスト対象メソッドを実行
        $result = FunctionValidator::build($this->definitions);

        // 結果の検証
        $this->assertInstanceOf(FunctionWrapValidator::class, $result);

        // 実際に機能するか検証するにはValidatorFunctionWrapHandlerが必要なため、
        // ここではインスタンスタイプの確認のみとする
    }

    /**
     * FunctionValidator::buildメソッドが、
     * PLAINモードのバリデーターを正しく作成することを検証
     */
    #[Test]
    public function buildShouldCreatePlainValidator(): void
    {
        // 実際のPlainValidatorオブジェクトを作成
        $validator = new PlainValidator(fn($value) => 'plain_' . $value);

        // FunctionValidatorDefinitionを作成し登録
        $validatorQueue = new FunctionValidatorDefinition([$validator]);
        $this->definitions->register($validatorQueue);

        // テスト対象メソッドを実行
        $result = FunctionValidator::build($this->definitions);

        // 結果の検証
        $this->assertInstanceOf(FunctionPlainValidator::class, $result);

        // 実際に機能するか検証（バリデーション関数が実行されることを確認）
        $output = $result->validate('test');
        $this->assertEquals('plain_test', $output);
    }

    /**
     * FunctionValidatorDefinitionが登録されていない場合、
     * 例外が発生することを検証
     */
    #[Test]
    public function buildShouldThrowExceptionWhenDefinitionNotSet(): void
    {
        // FunctionValidatorDefinitionを登録せずに実行
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('FunctionalValidatorQueueDefinition is not set.');

        // テスト対象メソッドを実行
        FunctionValidator::build($this->definitions);
    }

    /**
     * キューが空の場合に例外が発生することを検証
     */
    #[Test]
    public function buildShouldThrowExceptionWhenQueueIsEmpty(): void
    {
        // 空のキューを登録
        $emptyQueue = new FunctionValidatorDefinition([]);
        $this->definitions->register($emptyQueue);

        // 例外を期待
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Can\'t shift from an empty datastructure');

        // テスト対象メソッドを実行
        FunctionValidator::build($this->definitions);
    }
}
