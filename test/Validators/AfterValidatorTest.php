<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ArrayIterator;

/**
 * AfterValidatorのテストクラス
 *
 * AfterValidatorは次のハンドラーを先に実行し、その結果に対して
 * 自身のバリデーションを適用する役割を持つ
 */
#[CoversClass(AfterValidator::class)]
class AfterValidatorTest extends TestCase
{
    /**
     * ハンドラーなしでバリデーションが正しく実行されることを確認
     */
    #[Test]
    public function shouldExecuteValidationAndReturnValueWhenNoHandlerProvided(): void
    {
        // Arrange
        $validator = new AfterValidator(fn($value) => $value . '_after');
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
        $validator = new AfterValidator(fn($value) => $value . '_after');
        $value = 'test';

        // 実際のハンドラーを作成
        // 次のバリデータとしてBeforeValidatorを使用する
        $nextValidator = new BeforeValidator(fn($v) => $v . '_next');
        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$nextValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // 処理の流れ:
        // 1. AfterValidator: 次のハンドラーを先に実行
        // 2. BeforeValidator (nextValidator): 'test' -> 'test_next'
        // 3. AfterValidator: 'test_next' + '_after' -> 'test_next_after'
        $this->assertEquals('test_next_after', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
