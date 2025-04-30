<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Core\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ArrayIterator;

/**
 * FunctionPlainValidatorのテストクラス
 *
 * FunctionPlainValidatorは単純に関数を実行するバリデータ
 * ハンドラーが指定された場合でも無視して自身の処理のみを行う
 */
#[CoversClass(FunctionPlainValidator::class)]
class FunctionPlainValidatorTest extends TestCase
{
    /**
     * バリデーション関数が正しく実行されることを確認
     */
    #[Test]
    public function shouldExecuteValidationFunction(): void
    {
        // Arrange
        $validator = new FunctionPlainValidator(fn($value) => $value . '_plain');
        $value = 'test';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('test_plain', $result);
    }

    /**
     * ハンドラーが渡された場合でも無視して自身のバリデーションのみを実行することを確認
     */
    #[Test]
    public function shouldIgnoreHandlerAndOnlyExecuteSelfValidation(): void
    {
        // Arrange
        $validator = new FunctionPlainValidator(fn($value) => $value . '_plain');
        $value = 'test';

        // ダミーのハンドラーを作成（このハンドラーは呼ばれないはず）
        $mockValidator = $this->createMock(Validatorable::class);
        $mockValidator->expects($this->never())
            ->method('validate');

        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$mockValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // ハンドラーが無視され、FunctionPlainValidatorのみが実行される
        $this->assertEquals('test_plain', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
