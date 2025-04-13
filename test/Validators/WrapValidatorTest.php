<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use LogicException;
use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\WrapValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ArrayIterator;
use PhpValueObject\Validators\Validatorable;

/**
 * WrapValidatorのテストクラス
 *
 * 使用例:
 * ```php
 * #[WrapValidator(function($value, $handler) {
 *     if ($value === '') {
 *         return $value; // 後続の処理を実行しない
 *     }
 *     return $handler($value); // 後続の処理を実行
 * })]
 * public string $value;
 * ```
 */
#[CoversClass(WrapValidator::class)]
class WrapValidatorTest extends TestCase
{
    /**
     * getMode()メソッドのテスト
     * WRAPモードを返すことを確認
     */
    #[Test]
    public function testGetModeReturnsWrapMode(): void
    {
        $validator = new WrapValidator(fn($value, $handler) => $value);
        $this->assertSame(ValidatorMode::WRAP, $validator->getMode());
    }

    /**
     * validate()メソッドのテスト
     * handlerがnullの場合にLogicExceptionが発生することを確認
     */
    #[Test]
    public function testValidateThrowsLogicExceptionWhenHandlerIsNull(): void
    {
        $validator = new WrapValidator(fn($value, $handler) => strtoupper($value));
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('WrapValidator must be executed with a handler.');
        $validator->validate('test value');
    }

    /**
     * validate()メソッドのテスト
     * バリデータが後続処理を制御できることを確認
     */
    #[Test]
    public function testValidateCanControlNextValidation(): void
    {
        // 空文字列の場合は後続処理をスキップ、それ以外は後続処理を実行
        $validator = new WrapValidator(function ($value, ValidatorFunctionWrapHandler $handler) {
            if ($value === '') {
                return $value;
            }
            return $handler($value);
        });

        /**
         * 後続の処理として小文字化を行うバリデータを設定
         * @phpstan-var ArrayIterator<int,Validatorable> $validators
         * */
        $validators = new ArrayIterator([new AfterValidator(fn($v) => strtolower($v))]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        // 空文字列の場合は後続処理がスキップされる
        $result1 = $validator->validate('', $handler);
        $this->assertSame('', $result1);

        // 通常の値は後続処理が実行される（小文字化される）
        $result2 = $validator->validate('TEST', $handler);
        $this->assertSame('test', $result2);
    }
}
