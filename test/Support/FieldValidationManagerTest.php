<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validators\FieldValidator;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class TestClass
{
    #[BeforeValidator([TestValidator::class, 'validateLength'])]
    #[AfterValidator([TestValidator::class, 'formatName'])]
    public string $name;
}

class FieldValidationManagerTest extends TestCase
{
    private FieldValidationManager $managerWithAttributes;
    private FieldValidationManager $managerWithFieldValidators;
    private FieldValidationManager $managerWithBoth;
    private ReflectionProperty $property;

    protected function setUp(): void
    {
        $class = new TestClass();
        $this->property = new ReflectionProperty($class, 'name');

        // 属性のみを使用したマネージャー
        $this->managerWithAttributes = FieldValidationManager::createFromProperty($this->property);

        // FieldValidatorのみを使用したマネージャー
        $beforeValidator = new FieldValidator('name', 'before');
        $beforeValidator->setValidator(fn(string $value) => TestValidator::validateLength($value));
        $afterValidator = new FieldValidator('name', 'after');
        $afterValidator->setValidator(fn(string $value) => TestValidator::formatName($value));
        $this->managerWithFieldValidators = FieldValidationManager::createFromProperty(
            $this->property,
            [$beforeValidator, $afterValidator],
        );

        // 属性とFieldValidatorを組み合わせたマネージャー
        $additionalBeforeValidator = new FieldValidator('name', 'before');
        $additionalBeforeValidator->setValidator(
            fn(string $value) => strlen($value) > 5 ? $value : throw new ValidationException(
                '6文字以上必要です',
            ),
        );
        $this->managerWithBoth = FieldValidationManager::createFromProperty(
            $this->property,
            [$additionalBeforeValidator],
        );
    }

    /**
     * バリデーション失敗のテスト
     * 属性を使用したバリデーションで3文字未満の入力を検証
     */
    public function testValidationThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithAttributes->processValidation('ab');
    }

    /**
     * バリデーション成功のテスト
     * 属性を使用したバリデーションで3文字以上の入力を検証し、
     * その後文字列の先頭を大文字に変換
     */
    public function testValidationSuccess(): void
    {
        $result = $this->managerWithAttributes->processValidation('abc');
        $this->assertEquals('Abc', $result);
    }

    /**
     * バリデーション順序のテスト
     * 属性を使用したバリデーションの実行順序を検証
     */
    public function testValidationOrder(): void
    {
        $result = $this->managerWithAttributes->processValidation('john');
        $this->assertEquals('John', $result);
    }

    /**
     * FieldValidatorのバリデーションテスト
     * 入力値の長さを検証し、文字列の先頭を大文字に変換
     */
    public function testFieldValidation(): void
    {
        // バリデーション失敗のケース
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithFieldValidators->processValidation('ab');
    }

    /**
     * FieldValidatorのバリデーション成功テスト
     */
    public function testFieldValidationSuccess(): void
    {
        $result = $this->managerWithFieldValidators->processValidation('john');
        $this->assertEquals('John', $result);
    }

    /**
     * 属性とFieldValidatorの組み合わせテスト
     * BeforeValidator属性とFieldValidatorが両方適用される
     */
    public function testCombinedValidation(): void
    {
        // 最初のバリデーション（3文字以上）は通過するが、2番目のバリデーション（6文字以上）で失敗
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('6文字以上必要です');
        $this->managerWithBoth->processValidation('abcde');
    }

    /**
     * 属性とFieldValidatorの組み合わせテスト（成功ケース）
     * 全てのバリデーションを通過
     */
    public function testCombinedValidationSuccess(): void
    {
        $result = $this->managerWithBoth->processValidation('abcdef');
        $this->assertEquals('Abcdef', $result);
    }
}
