<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Helpers\ValidatorHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

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
        // ValidatorHelperを使用してSplQueueを作成
        $validators = ValidatorHelper::createValidatorQueue([$nextValidator]);
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

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
