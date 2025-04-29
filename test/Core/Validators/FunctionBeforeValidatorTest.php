<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ArrayIterator;

/**
 * FunctionBeforeValidatorのテストクラス
 *
 * FunctionBeforeValidatorは自身のバリデーションを実行した後、
 * 次のハンドラーに結果を渡す役割を持つ
 */
#[CoversClass(FunctionBeforeValidator::class)]
class FunctionBeforeValidatorTest extends TestCase
{
    /**
     * ハンドラーなしでバリデーションが正しく実行されることを確認
     */
    #[Test]
    public function shouldExecuteValidationAndReturnValueWhenNoHandlerProvided(): void
    {
        // Arrange
        $validator = new FunctionBeforeValidator(fn($value) => $value . '_before');
        $value = 'test';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('test_before', $result);
    }

    /**
     * 自身のバリデーションを実行した後、次のハンドラーに結果を渡すことを確認
     */
    #[Test]
    public function shouldExecuteValidationFirstThenCallHandler(): void
    {
        // Arrange
        $validator = new FunctionBeforeValidator(fn($value) => $value . '_before');
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
        // 1. FunctionBeforeValidator: 'test' -> 'test_before'
        // 2. FunctionAfterValidator (nextValidator): 'test_before' + '_next' -> 'test_before_next'
        $this->assertEquals('test_before_next', $result);
    }

    /**
     * 配列形式のバリデータが正しく解決されることを確認
     */
    #[Test]
    public function shouldResolveArrayValidator(): void
    {
        // テスト用のバリデーション関数を持つクラス
        $validatorClass = new class {
            public static function appendText(string $value): string
            {
                return $value . '_validated';
            }
        };

        // Arrange
        $validator = new FunctionBeforeValidator([get_class($validatorClass), 'appendText']);
        $value = 'test';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('test_validated', $result);
    }

    /**
     * 文字列形式のバリデータ（グローバル関数）が正しく解決されることを確認
     */
    #[Test]
    public function shouldResolveStringValidator(): void
    {
        // Arrange
        $validator = new FunctionBeforeValidator('strtoupper');
        $value = 'test';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('TEST', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
