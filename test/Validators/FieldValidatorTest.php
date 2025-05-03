<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Validators\ValidatorMode;
use PhpValueObject\Validators\ValidatorCallable;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PhpValueObject\Core\Validators\FunctionValidator;
use ReflectionClass;
use RuntimeException;

/**
 * @phpstan-import-type validator_callable from ValidatorCallable
 */
final class FieldValidatorTest extends TestCase
{
    /**
     * コンストラクタでフィールド名が正しく設定されることを確認する
     */
    #[Test]
    public function constructorShouldSetFieldName(): void
    {
        $fieldValidator = new FieldValidator('test_field');
        $this->assertSame('test_field', $fieldValidator->field);
    }

    /**
     * コンストラクタでモードが正しく設定されることを確認する
     * @param ValidatorMode $mode
     * @param ValidatorMode $expectedMode
     */
    #[Test]
    #[DataProvider('modeProvider')]
    public function constructorShouldSetMode(ValidatorMode $mode, ValidatorMode $expectedMode): void
    {
        $fieldValidator = new FieldValidator('test_field', $mode);

        // private プロパティ 'mode' をリフレクションで取得して検証
        $reflection = new ReflectionClass($fieldValidator);
        $modeProperty = $reflection->getProperty('mode');
        $modeProperty->setAccessible(true); // private プロパティにアクセス可能にする

        $this->assertSame($expectedMode, $modeProperty->getValue($fieldValidator));
    }

    /**
     * モードと期待されるクラス名のデータプロバイダ
     * @return array<string, array{ValidatorMode, ValidatorMode, class-string<FunctionValidator>}>
     */
    public static function modeProvider(): array
    {
        return [
            'plain mode' => [ValidatorMode::PLAIN, ValidatorMode::PLAIN, FunctionPlainValidator::class,],
            'wrap mode' => [ValidatorMode::WRAP, ValidatorMode::WRAP, FunctionWrapValidator::class],
            'before mode' => [ValidatorMode::BEFORE, ValidatorMode::BEFORE, FunctionBeforeValidator::class,],
            'after mode (default)' => [ValidatorMode::AFTER, ValidatorMode::AFTER, FunctionAfterValidator::class,],
        ];
    }

    /**
     * FieldValidatorがValidatorCallableインターフェースを実装していることを確認する
     */
    #[Test]
    public function shouldImplementValidatorCallableInterface(): void
    {
        $fieldValidator = new FieldValidator('test_field');
        $this->assertInstanceOf(ValidatorCallable::class, $fieldValidator);
    }

    /**
     * setCallableメソッドがcallableを正しく設定し、getCallableで取得できることを確認する
     */
    #[Test]
    public function setCallableShouldSetCallableCorrectly(): void
    {
        $fieldValidator = new FieldValidator('test_field');
        $callable = function ($value) {
            return $value !== null;
        };

        $result = $fieldValidator->setCallable($callable);

        // メソッドチェーンのためにselfが返されることを確認
        $this->assertSame($fieldValidator, $result);

        // getCallableで設定したcallableが返されることを確認
        $this->assertSame($callable, $fieldValidator->resolveValidator());
    }

    /**
     * callableが設定されていない場合、getCallableが例外をスローすることを確認する
     */
    #[Test]
    public function getCallableShouldThrowExceptionWhenCallableIsNotSet(): void
    {
        $fieldValidator = new FieldValidator('test_field');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Validator callable is not set');

        $fieldValidator->resolveValidator();
    }

    /**
     * getModeがコンストラクタで設定したモードを返すことを確認する
     */
    #[Test]
    public function getModeShouldReturnCorrectMode(): void
    {
        $fieldValidator = new FieldValidator('test_field', ValidatorMode::PLAIN);
        $this->assertSame(ValidatorMode::PLAIN, $fieldValidator->getMode());
    }
}
