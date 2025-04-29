<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Validators\FunctionalValidatorMode;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PhpValueObject\Core\Validators\FunctionValidator;
use ReflectionClass;

/**
 * @phpstan-import-type validator_callable from \PhpValueObject\Validators\Validatorable
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
     * @param FunctionalValidatorMode $mode
     * @param FunctionalValidatorMode $expectedMode
     */
    #[Test]
    #[DataProvider('modeProvider')]
    public function constructorShouldSetMode(
        FunctionalValidatorMode $mode,
        FunctionalValidatorMode $expectedMode,
    ): void {
        $fieldValidator = new FieldValidator('test_field', $mode);

        // private プロパティ 'mode' をリフレクションで取得して検証
        $reflection = new ReflectionClass($fieldValidator);
        $modeProperty = $reflection->getProperty('mode');
        $modeProperty->setAccessible(true); // private プロパティにアクセス可能にする

        $this->assertSame($expectedMode, $modeProperty->getValue($fieldValidator));
    }

    /**
     * getValidatorが正しいFunctionValidatorインスタンスを返すことを確認する
     * @param FunctionalValidatorMode $mode
     * @param FunctionalValidatorMode $expectedMode
     * @param class-string<FunctionValidator> $expectedClass
     */
    #[Test]
    #[DataProvider('modeProvider')]
    public function getValidatorShouldReturnCorrectInstance(
        FunctionalValidatorMode $mode,
        FunctionalValidatorMode $expectedMode,
        string $expectedClass,
    ): void {
        $fieldValidator = new FieldValidator('test_field', $mode);
        $dummyCallable = fn($v) => $v;

        $functionValidator = $fieldValidator->getValidator($dummyCallable);

        $this->assertInstanceOf($expectedClass, $functionValidator);
    }

    /**
     * モードと期待されるクラス名のデータプロバイダ
     * @return array<string, array{FunctionalValidatorMode, FunctionalValidatorMode, class-string<FunctionValidator>}>
     */
    public static function modeProvider(): array
    {
        return [
            'plain mode' => [
                FunctionalValidatorMode::PLAIN,
                FunctionalValidatorMode::PLAIN,
                FunctionPlainValidator::class,
            ],
            'wrap mode' => [FunctionalValidatorMode::WRAP, FunctionalValidatorMode::WRAP, FunctionWrapValidator::class],
            'before mode' => [
                FunctionalValidatorMode::BEFORE,
                FunctionalValidatorMode::BEFORE,
                FunctionBeforeValidator::class,
            ],
            'after mode (default)' => [
                FunctionalValidatorMode::AFTER,
                FunctionalValidatorMode::AFTER,
                FunctionAfterValidator::class,
            ],
        ];
    }
}
