<?php

declare(strict_types=1);

namespace PhSculptis\Test\Core\Validators;

use PhSculptis\Core\Definitions\FunctionValidatorDefinition;
use PhSculptis\Core\Validators\FunctionAfterValidator;
use PhSculptis\Core\Validators\FunctionBeforeValidator;
use PhSculptis\Validators\BeforeValidator;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;
use PhSculptis\Validators\ValidatorQueue;
use PhSculptis\Core\ValidatorDefinitions;
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
        $nextValidator = new BeforeValidator(fn($v) => $v . '_next');
        $functionValidatorDefinition = new FunctionValidatorDefinition([$nextValidator]);

        // ValidatorQueueを直接作成
        $validators = new ValidatorQueue([FunctionBeforeValidator::class]);
        $definitions = (new ValidatorDefinitions())->register($functionValidatorDefinition);
        $handler = new ValidatorFunctionWrapHandler($validators, $definitions);

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
