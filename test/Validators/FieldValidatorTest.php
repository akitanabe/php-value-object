<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Core\Validators\PlainFunctionValidator;
use PhpValueObject\Core\Validators\WrapFunctionValidator;
use PhpValueObject\Core\Validators\BeforeFunctionValidator;
use PhpValueObject\Core\Validators\AfterFunctionValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PhpValueObject\Validators\FunctionValidator;
use ReflectionClass;

/**
 * @phpstan-import-type field_validator_mode from \PhpValueObject\Validators\FieldValidator
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
     * @param field_validator_mode $mode
     * @param field_validator_mode $expectedMode
     */
    #[Test]
    #[DataProvider('modeProvider')]
    public function constructorShouldSetMode(string $mode, string $expectedMode): void
    {
        $fieldValidator = new FieldValidator('test_field', $mode);

        // private プロパティ 'mode' をリフレクションで取得して検証
        $reflection = new ReflectionClass($fieldValidator);
        $modeProperty = $reflection->getProperty('mode');
        $modeProperty->setAccessible(true); // private プロパティにアクセス可能にする

        $this->assertSame($expectedMode, $modeProperty->getValue($fieldValidator));
    }

    /**
     * getValidatorが正しいFunctionValidatorインスタンスを返すことを確認する
     * @param field_validator_mode $mode
     * @param field_validator_mode $expectedMode
     * @param class-string<FunctionValidator> $expectedClass
     */
    #[Test]
    #[DataProvider('modeProvider')]
    public function getValidatorShouldReturnCorrectInstance(
        string $mode,
        string $expectedMode,
        string $expectedClass,
    ): void {
        $fieldValidator = new FieldValidator('test_field', $mode);
        $dummyCallable = fn($v) => $v;

        $functionValidator = $fieldValidator->getValidator($dummyCallable);

        $this->assertInstanceOf($expectedClass, $functionValidator);
    }

    /**
     * モードと期待されるクラス名のデータプロバイダ
     * @return array<string, array{field_validator_mode, field_validator_mode, class-string<FunctionValidator>}>
     */
    public static function modeProvider(): array
    {
        return [
            'plain mode' => ['plain', 'plain', PlainFunctionValidator::class],
            'wrap mode' => ['wrap', 'wrap', WrapFunctionValidator::class],
            'before mode' => ['before', 'before', BeforeFunctionValidator::class],
            'after mode (default)' => ['after', 'after', AfterFunctionValidator::class],
        ];
    }
}
