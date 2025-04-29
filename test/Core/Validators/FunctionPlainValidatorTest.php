<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
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

    /**
     * 配列形式のバリデータが正しく解決されることを確認
     */
    #[Test]
    public function shouldResolveArrayValidator(): void
    {
        // テスト用のバリデーション関数を持つクラス
        $validatorClass = new class {
            public static function processValue(string $value): string
            {
                return $value . '_processed';
            }
        };

        // Arrange
        $validator = new FunctionPlainValidator([get_class($validatorClass), 'processValue']);
        $value = 'test';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('test_processed', $result);
    }

    /**
     * 文字列形式のバリデータ（グローバル関数）が正しく解決されることを確認
     */
    #[Test]
    public function shouldResolveStringValidator(): void
    {
        // Arrange
        $validator = new FunctionPlainValidator('strtolower');
        $value = 'TEST';

        // Act
        $result = $validator->validate($value);

        // Assert
        $this->assertEquals('test', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
