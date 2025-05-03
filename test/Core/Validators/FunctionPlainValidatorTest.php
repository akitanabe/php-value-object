<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Core\Validators\Validatorable;
use PhpValueObject\Helpers\ValidatorHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use SplQueue;

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

        // ハンドラーを使用しても無視されることを確認するために
        // 次のバリデータとしてFunctionAfterValidatorを使用する
        $nextValidator = new FunctionAfterValidator(fn($v) => $v . '_next');
        // ValidatorHelperを使用してSplQueueを作成
        $validators = ValidatorHelper::createValidatorQueue([$nextValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // 処理の流れ:
        // 1. FunctionPlainValidator: 'test' -> 'test_plain'
        // 2. 次のハンドラーは無視される
        $this->assertEquals('test_plain', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
