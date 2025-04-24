<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ArrayIterator;

/**
 * BeforeValidatorのテストクラス
 *
 * BeforeValidatorは自身のバリデーション後に次のハンドラーを呼び出す役割を持つ
 */
#[CoversClass(BeforeValidator::class)]
class BeforeValidatorTest extends TestCase
{
    /**
     * ハンドラーなしでバリデーションが正しく実行されることを確認
     */
    #[Test]
    public function shouldExecuteValidationAndReturnValueWhenNoHandlerProvided(): void
    {
        // Arrange
        $validator = new BeforeValidator(fn($value) => $value . '_validated');
        $value = 'test';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('test_validated', $result);
    }

    /**
     * バリデーション後に次のハンドラーが正しく呼び出されることを確認
     */
    #[Test]
    public function shouldExecuteValidationAndThenCallNextHandler(): void
    {
        // Arrange
        $validator = new BeforeValidator(fn($value) => $value . '_before');
        $value = 'test';

        // 実際のハンドラーを作成
        // 次のバリデータとしてAfterValidatorを使用する
        $nextValidator = new AfterValidator(fn($v) => $v . '_after');
        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$nextValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // 処理の流れ:
        // 1. BeforeValidator: 'test' -> 'test_before'
        // 2. AfterValidator:  'test_before' -> 'test_before_after'
        $this->assertEquals('test_before_after', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
