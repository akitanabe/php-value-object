<?php

declare(strict_types=1);

namespace PhSculptis\Test\Core\Validators;

use LogicException;
use PhSculptis\Core\Definitions\FunctionValidatorDefinition;
use PhSculptis\Core\Validators\FunctionWrapValidator;
use PhSculptis\Core\Validators\FunctionAfterValidator;
use PhSculptis\Validators\AfterValidator;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;
use PhSculptis\Validators\ValidatorQueue;
use PhSculptis\Core\ValidatorDefinitions;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * テスト用のバリデーション関数
 *
 * @param string $value バリデーション対象の値
 * @param callable $next 次のハンドラー
 * @return string バリデーション後の値
 */
function testWrapFunction(string $value, callable $next): string
{
    return '(' . $next($value) . ')';
}


/**
 * FunctionWrapValidatorのテストクラス
 *
 * FunctionWrapValidatorは次のハンドラーを包み込み、
 * 処理の前後に独自のバリデーション処理を適用する役割を持つ
 */
#[CoversClass(FunctionWrapValidator::class)]
class FunctionWrapValidatorTest extends TestCase
{
    /**
     * ハンドラーがない場合に例外が発生することを確認
     */
    #[Test]
    public function shouldThrowExceptionWhenNoHandlerProvided(): void
    {
        // Arrange
        $validator = new FunctionWrapValidator(fn($value, $next) => $value . '_wrapped');
        $value = 'test';

        // Act & Assert
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('FunctionWrapValidator must be executed with a handler.');
        $validator->validate($value);
    }

    /**
     * 次のハンドラーを包み込んでバリデーションが実行されることを確認
     */
    #[Test]
    public function shouldWrapHandlerWithValidation(): void
    {
        // Arrange
        // 通常の関数でラップバリデータを作成
        $validator = new FunctionWrapValidator(
            fn($value, $next) => $next($value . '_before') . '_after',
        );
        $value = 'test';

        // 実際のハンドラーを作成
        // 次のバリデータとしてFunctionAfterValidatorを使用する
        $nextValidator = new AfterValidator(fn($v) => $v . '_next');
        $functionValidatorDefinition = new FunctionValidatorDefinition([$nextValidator]);

        // ValidatorQueueを直接作成
        $validators = new ValidatorQueue([FunctionAfterValidator::class]);
        $definitions = (new ValidatorDefinitions())->register($functionValidatorDefinition);
        $handler = new ValidatorFunctionWrapHandler($validators, $definitions);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // 処理の流れ:
        // 1. 'test' -> 'test_before' -> FunctionAfterValidator -> 'test_before_next' -> + '_after' -> 'test_before_next_after'
        $this->assertEquals('test_before_next_after', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
