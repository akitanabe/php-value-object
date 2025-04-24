<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Validators\PlainValidator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use ArrayIterator;

/**
 * PlainValidatorのテストクラス
 *
 * PlainValidatorは自身のバリデーションのみを実行し、
 * 後続のバリデータを呼び出さずにバリデーションチェーンを終了させる役割を持つ
 */
#[CoversClass(PlainValidator::class)]
class PlainValidatorTest extends TestCase
{
    /**
     * PLAINモードを返すことを確認
     */
    #[Test]
    public function shouldReturnPlainMode(): void
    {
        $validator = new PlainValidator(fn($value) => $value);
        $this->assertEquals(ValidatorMode::PLAIN, $validator->getMode());
    }

    /**
     * バリデーション処理が正しく実行されることを確認
     */
    #[Test]
    public function shouldExecuteValidatorFunction(): void
    {
        $called = false;
        $validator = new PlainValidator(function ($value) use (&$called) {
            $called = true;
            return $value . '_validated';
        });

        $result = $validator->validate('test');

        $this->assertTrue($called);
        $this->assertEquals('test_validated', $result);
    }

    /**
     * ハンドラーが渡された場合でも、次のハンドラーを呼び出さないことを確認
     */
    #[Test]
    public function shouldNotCallNextHandlerWhenProvided(): void
    {
        // Arrange
        $validator = new PlainValidator(fn($value) => $value . '_plain');
        $value = 'test';

        // 実際のハンドラーを作成
        // このバリデータは呼び出されないはず
        $nextValidator = new AfterValidator(fn($v) => $v . '_should_not_execute');
        /** @var ArrayIterator<int, Validatorable> $validators */
        $validators = new ArrayIterator([$nextValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // Act
        $result = $validator->validate($value, $handler);

        // Assert
        // AfterValidatorが呼び出されていない場合、'_should_not_execute'は付加されない
        $this->assertEquals('test_plain', $result);
        $this->assertStringNotContainsString('_should_not_execute', $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
