<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ArrayIterator;

/**
 * FunctionAfterValidatorのテストクラス
 *
 * FunctionAfterValidatorは次のハンドラーを先に実行し、その結果に対して
 * 自身のバリデーションを適用する役割を持つ
 */
#[CoversClass(FunctionAfterValidator::class)]
class FunctionAfterValidatorTest extends TestCase
{
    /**
     * ハンドラーなしでバリデーションが正しく実行されることを確認
     */
    #[Test]
    public function shouldExecuteValidationAndReturnValueWhenNoHandlerProvided(): void
    {
        // Arrange
        $validator = new FunctionAfterValidator(fn($value) => $value . '_after');
        $value = 'test';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('test_after', $result);
    }

    /**
     * 次のハンドラーを先に実行し、その結果に対してバリデーションが適用されることを確認
     */
    #[Test]
    public function shouldCallHandlerFirstThenExecuteValidation(): void
    {
        // Arrange
        $validator = new FunctionAfterValidator(fn($value) => $value . '_after');
        $value = 'test';

        // 実際のハンドラーを作成
        // 次のバリデータとしてFunctionBeforeValidatorを使用する
        $nextValidator = new FunctionBeforeValidator(fn($v) => $v . '_next');
        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$nextValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // 処理の流れ:
        // 1. FunctionAfterValidator: 次のハンドラーを先に実行
        // 2. FunctionBeforeValidator (nextValidator): 'test' -> 'test_next'
        // 3. FunctionAfterValidator: 'test_next' + '_after' -> 'test_next_after'
        $this->assertEquals('test_next_after', $result);
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
        $validator = new FunctionAfterValidator([get_class($validatorClass), 'appendText']);
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
        // テスト用のグローバル関数
        // Note: 実際のテストでは、既存の関数（例：strtoupper）を使用するか、
        // または関数をモックすることをお勧めします

        // Arrange
        $validator = new FunctionAfterValidator('strtoupper');
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
