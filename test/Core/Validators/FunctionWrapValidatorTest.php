<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ArrayIterator;
use LogicException;

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
        $validator = new FunctionWrapValidator(
            fn($value, $next) => 'before_' . $next($value) . '_after',
        );
        $value = 'test';

        // 実際のハンドラーを作成
        // 次のバリデータとしてFunctionAfterValidatorを使用する
        $nextValidator = new FunctionAfterValidator(fn($v) => $v . '_next');
        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$nextValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // 処理の流れ:
        // 1. FunctionWrapValidator: 'before_' + (次のハンドラーの結果) + '_after'
        // 2. FunctionAfterValidator (nextValidator): 'test' + '_next' -> 'test_next'
        // 3. 最終結果: 'before_test_next_after'
        $this->assertEquals('before_test_next_after', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
