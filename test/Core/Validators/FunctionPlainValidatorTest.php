<?php

declare(strict_types=1);

namespace PhSculptis\Test\Core\Validators;

use PhSculptis\Core\Definitions\FunctionValidatorDefinition;
use PhSculptis\Core\Validators\FunctionPlainValidator;
use PhSculptis\Core\Validators\FunctionAfterValidator;
use PhSculptis\Validators\AfterValidator;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;
use PhSculptis\Validators\ValidatorQueue;
use PhSculptis\Core\ValidatorDefinitions;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

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
        // 1. FunctionPlainValidator: 'test' -> 'test_plain'
        // 2. 次のハンドラーは無視される
        $this->assertEquals('test_plain', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
