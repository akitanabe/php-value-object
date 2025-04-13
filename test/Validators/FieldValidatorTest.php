<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Enums\ValidatorMode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FieldValidatorTest extends TestCase
{
    /**
     * コンストラクタでフィールド名とデフォルトモードが正しく設定されることを確認する
     */
    #[Test]
    public function constructorShouldSetFieldNameAndDefaultMode(): void
    {
        $validator = new FieldValidator('test_field');

        $this->assertSame('test_field', $validator->field);
        $this->assertSame(ValidatorMode::AFTER, $validator->getMode());
    }

    /**
     * コンストラクタで明示的に指定したモードが正しく設定されることを確認する
     */
    #[Test]
    public function constructorShouldSetCustomModeWhenSpecified(): void
    {
        $validator = new FieldValidator('test_field', ValidatorMode::BEFORE);

        $this->assertSame(ValidatorMode::BEFORE, $validator->getMode());
    }

    /**
     * setValidatorでクロージャが正しく保存されることを確認する
     */
    #[Test]
    public function setValidatorShouldStoreClosure(): void
    {
        $validator = new FieldValidator('test_field');
        $closure = fn(mixed $value): mixed => $value;

        $validator->setValidator($closure);

        // クロージャが正しく設定されたかをvalidateメソッドの動作で確認
        $result = $validator->validate('test');
        $this->assertSame('test', $result);
    }

    /**
     * validateメソッドでバリデーション関数が正しく実行されることを確認する
     */
    #[Test]
    public function validateShouldExecuteValidationFunction(): void
    {
        $validator = new FieldValidator('test_field');
        $validator->setValidator(fn(mixed $value): string => strtoupper($value));

        $result = $validator->validate('test');

        $this->assertSame('TEST', $result);
    }

    /**
     * validateメソッドで値の変換が正しく処理されることを確認する
     */
    #[Test]
    public function validateShouldHandleValueTransformation(): void
    {
        $validator = new FieldValidator('test_field');
        $validator->setValidator(fn(mixed $value): int => (int) $value + 1);

        $result = $validator->validate('123');

        $this->assertSame(124, $result);
    }

    /**
     * validateメソッドでバリデーション失敗時に例外が投げられることを確認する
     */
    #[Test]
    public function validateShouldThrowExceptionOnValidationFailure(): void
    {
        $validator = new FieldValidator('test_field');
        $validator->setValidator(function (mixed $value): mixed {
            if (!is_string($value)) {
                throw new ValidationException('Value must be string');
            }
            return $value;
        });

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Value must be string');

        $validator->validate(123);
    }

    /**
     * getModeで設定されたバリデーションモードが正しく取得できることを確認する
     */
    #[Test]
    public function getModeShouldReturnValidationMode(): void
    {
        $validator = new FieldValidator('test_field', ValidatorMode::BEFORE);

        $this->assertSame(ValidatorMode::BEFORE, $validator->getMode());
    }
}
