<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use ArrayIterator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Core\Validators\Validatorable;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

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
    /**
     * PLAINモードのvalidatorが設定されている場合、他のvalidatorが実行されないことを確認
     */
    #[Test]
    public function shouldStopChainWhenPlainValidatorExists(): void
    {
        // 実際のPlainValidatorとAfterValidatorを使用
        $plainValidator = new FunctionPlainValidator(fn($value) => $value . '_plain');
        $afterValidator = new FunctionAfterValidator(fn($value) => $value . '_after');

        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$plainValidator, $afterValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

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
        $beforeValidator = new FunctionBeforeValidator(fn($value) => $value . '_before');
        $afterValidator = new FunctionAfterValidator(fn($value) => $value . '_after');

        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$beforeValidator, $afterValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

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
        $beforeValidator = new FunctionBeforeValidator(fn($value) => $value . '_before');
        $plainValidator = new FunctionPlainValidator(fn($value) => $value . '_plain');

        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$beforeValidator, $plainValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

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
        $beforeValidator = new FunctionBeforeValidator(fn($value) => $value . '_before');
        $afterValidator = new FunctionAfterValidator(fn($value) => $value . '_after');

        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$beforeValidator, $afterValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

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
        // 実際のWrapValidatorを使用
        $wrapValidator = new FunctionWrapValidator(function ($value, $handler) {
            // 値を大文字に変換し、条件に応じて次のハンドラーを呼び出す
            $upperValue = strtoupper($value);
            // 値が'END'の場合は次のハンドラーを呼び出さない
            if ($upperValue === 'END') {
                return $upperValue;
            }
            // それ以外は次のハンドラーを呼び出す
            return $handler($upperValue);
        });

        // 次のバリデータとしてAfterValidatorを使用
        $afterValidator = new FunctionAfterValidator(fn($value) => $value . '_processed');
        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$wrapValidator, $afterValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

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
        // 実際のWrapValidatorを使用
        $wrapValidator = new FunctionWrapValidator(function ($value, $handler) {
            // 値を大文字に変換し、条件に応じて次のハンドラーを呼び出さない
            $upperValue = strtoupper($value);
            // 値が'END'の場合は次のハンドラーを呼び出さない
            if ($upperValue === 'END') {
                return $upperValue;
            }
            // それ以外は次のハンドラーを呼び出す
            return $handler($upperValue);
        });

        // 次のバリデータを追加（呼び出されないはず）
        $afterValidator = new FunctionAfterValidator(fn($value) => $value . '_should_not_execute');
        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$wrapValidator, $afterValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

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
