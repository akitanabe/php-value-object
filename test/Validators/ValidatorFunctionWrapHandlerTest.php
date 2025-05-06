<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Support\FunctionValidatorFactory;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\PlainValidator;
use PhpValueObject\Validators\WrapValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PhpValueObject\Validators\ValidatorQueue;

/**
 * ValidatorFunctionWrapHandlerのテストクラス
 *
 * ValidatorFunctionWrapHandlerはバリデータのチェーンを管理し、
 * 適切に次のハンドラーを作成して各バリデータに渡す役割を持つ。
 * Chain of Responsibilityパターンの実装として機能する。
 */
#[CoversClass(ValidatorFunctionWrapHandler::class)]
class ValidatorFunctionWrapHandlerTest extends TestCase
{
    protected ValidatorDefinitions $validatorDefinitions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validatorDefinitions = new ValidatorDefinitions();
    }

    /**
     * PLAINモードのvalidatorが設定されている場合、他のvalidatorが実行されないことを確認
     */
    #[Test]
    public function shouldStopChainWhenPlainValidatorExists(): void
    {
        // 実際のPlainValidatorとAfterValidatorを使用
        $plainValidator = new PlainValidator(fn($value) => $value . '_plain');
        $afterValidator = new AfterValidator(fn($value) => $value . '_after');

        $functionValidatorFactory = new FunctionValidatorFactory([], [$plainValidator, $afterValidator]);
        $validatorQueue = new ValidatorQueue($functionValidatorFactory->getValidators());

        $this->validatorDefinitions->register($functionValidatorFactory->createDefinition());
        $handler = new ValidatorFunctionWrapHandler($validatorQueue, $this->validatorDefinitions);

        $result = $handler('test');

        // PlainValidatorのみが実行され、AfterValidatorは実行されないはず
        $this->assertEquals('test_plain', $result);
        $this->assertStringNotContainsString('_after', $result);
    }

    /**
     * PLAINモードのvalidatorが設定されていない場合、他のvalidatorが通常通り実行されることを確認
     */
    #[Test]
    public function shouldExecuteAllValidatorsWhenNoPlainValidator(): void
    {
        // 実際のBeforeValidatorとAfterValidatorを使用
        $beforeValidator = new BeforeValidator(fn($value) => $value . '_before');
        $afterValidator = new AfterValidator(fn($value) => $value . '_after');

        $functionValidatorFactory = new FunctionValidatorFactory([], [$beforeValidator, $afterValidator]);
        $validatorQueue = new ValidatorQueue($functionValidatorFactory->getValidators());

        $this->validatorDefinitions->register($functionValidatorFactory->createDefinition());
        $handler = new ValidatorFunctionWrapHandler($validatorQueue, $this->validatorDefinitions);

        $result = $handler('test');

        // BeforeValidatorとAfterValidatorが順番に実行される
        // 1. BeforeValidator: 'test' -> 'test_before'
        // 2. AfterValidator: 'test_before' -> 'test_before_after'
        $this->assertEquals('test_before_after', $result);
    }

    /**
     * PLAINモードのバリデータが途中に配置されても正常に動作することを確認
     */
    #[Test]
    public function shouldWorkCorrectlyWithPlainValidatorInAnyPosition(): void
    {
        // 実際のバリデータを使用
        $beforeValidator = new BeforeValidator(fn($value) => $value . '_before');
        $plainValidator = new PlainValidator(fn($value) => $value . '_plain');

        $functionValidatorFactory = new FunctionValidatorFactory([], [$beforeValidator, $plainValidator]);
        $validatorQueue = new ValidatorQueue($functionValidatorFactory->getValidators());

        $this->validatorDefinitions->register($functionValidatorFactory->createDefinition());
        $handler = new ValidatorFunctionWrapHandler($validatorQueue, $this->validatorDefinitions);

        // 実行して結果を検証
        $result = $handler('test');

        // 処理の流れ:
        // 1. BeforeValidator: 'test' -> 'test_before'
        // 2. PlainValidator: 'test_before' -> 'test_before_plain'
        // PlainValidator後のバリデータは実行されない
        $this->assertEquals('test_before_plain', $result);
    }

    /**
     * 修正後の各バリデータの連携動作をテスト
     * BeforeValidator -> AfterValidator の順で処理が行われることを確認
     */
    #[Test]
    public function shouldExecuteValidatorsInChainWithModifiedImplementation(): void
    {
        // 実際のバリデータを使用
        $beforeValidator = new BeforeValidator(fn($value) => $value . '_before');
        $afterValidator = new AfterValidator(fn($value) => $value . '_after');

        $functionValidatorFactory = new FunctionValidatorFactory([], [$beforeValidator, $afterValidator]);
        $validatorQueue = new ValidatorQueue($functionValidatorFactory->getValidators());

        $this->validatorDefinitions->register($functionValidatorFactory->createDefinition());
        $handler = new ValidatorFunctionWrapHandler($validatorQueue, $this->validatorDefinitions);

        // 実行して結果を検証
        $result = $handler('test');

        // 処理の流れ：
        // 1. BeforeValidator: 'test' -> 'test_before' + 次のハンドラー呼び出し
        // 2. AfterValidator: 'test_before' + 次のハンドラーなし -> 'test_before_after'
        $this->assertEquals('test_before_after', $result);
    }

    /**
     * WrapValidatorがバリデータ関数に次のハンドラーを渡すことを確認
     */
    #[Test]
    public function shouldPassHandlerToWrapValidatorFunction(): void
    {
        // WrapValidatorとAfterValidatorを使用
        $wrapValidator = new WrapValidator(function ($value, $handler) {
            // 値を大文字に変換し、条件に応じて次のハンドラーを呼び出す
            $upperValue = strtoupper($value);
            // 値が'END'の場合は次のハンドラーを呼び出さない
            if ($upperValue === 'END') {
                return $upperValue;
            }
            // それ以外は次のハンドラーを呼び出す
            return $handler($upperValue);
        });
        $afterValidator = new AfterValidator(fn($value) => $value . '_processed');

        $functionValidatorFactory = new FunctionValidatorFactory([], [$wrapValidator, $afterValidator]);
        $validatorQueue = new ValidatorQueue($functionValidatorFactory->getValidators());

        $this->validatorDefinitions->register($functionValidatorFactory->createDefinition());
        $handler = new ValidatorFunctionWrapHandler($validatorQueue, $this->validatorDefinitions);

        // 実行して結果を検証
        $result = $handler('test');

        // 処理の流れ：
        // 1. WrapValidator: 'test' -> 'TEST' + 次のハンドラー呼び出し
        // 2. AfterValidator: 'TEST' -> 'TEST_processed'
        $this->assertEquals('TEST_processed', $result);
    }

    /**
     * WrapValidatorが条件に応じて次のハンドラーを呼び出さないことを確認
     */
    #[Test]
    public function shouldAllowWrapValidatorToSkipNextHandler(): void
    {
        // WrapValidatorとAfterValidatorを使用
        $wrapValidator = new WrapValidator(function ($value, $handler) {
            // 値を大文字に変換し、条件に応じて次のハンドラーを呼び出さない
            $upperValue = strtoupper($value);
            // 値が'END'の場合は次のハンドラーを呼び出さない
            if ($upperValue === 'END') {
                return $upperValue;
            }
            // それ以外は次のハンドラーを呼び出す
            return $handler($upperValue);
        });
        $afterValidator = new AfterValidator(fn($value) => $value . '_should_not_execute');

        $functionValidatorFactory = new FunctionValidatorFactory([], [$wrapValidator, $afterValidator]);
        $validatorQueue = new ValidatorQueue($functionValidatorFactory->getValidators());

        $this->validatorDefinitions->register($functionValidatorFactory->createDefinition());
        $handler = new ValidatorFunctionWrapHandler($validatorQueue, $this->validatorDefinitions);

        // 'end'を渡すと大文字化して'END'になり、次のハンドラーは呼び出されない
        $result = $handler('end');

        $this->assertEquals('END', $result);
        $this->assertStringNotContainsString('_should_not_execute', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
