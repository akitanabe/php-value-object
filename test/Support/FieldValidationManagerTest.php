<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Validation\BeforeValidator;
use PhpValueObject\Validation\AfterValidator;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validation\FieldValidator;
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
     * BeforeValidatorのテスト（バリデーション失敗）
     * 属性を使用したバリデーションで3文字未満の入力を検証
     */
    public function testBeforeValidationThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithAttributes->processBeforeValidation('ab');
    }

    /**
     * BeforeValidatorのテスト（バリデーション成功）
     * 属性を使用したバリデーションで3文字以上の入力を検証
     */
    public function testBeforeValidationSuccess(): void
    {
        $result = $this->managerWithAttributes->processBeforeValidation('abc');
        $this->assertEquals('abc', $result);
    }

    /**
     * AfterValidatorのテスト
     * 属性を使用したバリデーションで文字列の先頭を大文字に変換
     */
    public function testAfterValidation(): void
    {
        $result = $this->managerWithAttributes->processAfterValidation('john');
        $this->assertEquals('John', $result);
    }

    /**
     * バリデーション順序のテスト
     * 属性を使用したバリデーションの実行順序を検証
     */
    public function testValidationOrder(): void
    {
        $result = $this->managerWithAttributes->processBeforeValidation('john');
        $this->assertEquals('john', $result);

        $result = $this->managerWithAttributes->processAfterValidation($result);
        $this->assertEquals('John', $result);
    }

    /**
     * beforeモードのFieldValidatorのテスト
     * FieldValidatorを使用して入力値の長さを検証
     */
    public function testBeforeFieldValidation(): void
    {
        // バリデーション失敗のケース
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->managerWithFieldValidators->processBeforeValidation('ab');
    }

    /**
     * afterモードのFieldValidatorのテスト
     * FieldValidatorを使用して文字列の先頭を大文字に変換
     */
    public function testAfterFieldValidation(): void
    {
        $result = $this->managerWithFieldValidators->processAfterValidation('john');
        $this->assertEquals('John', $result);
    }

    /**
     * 属性とFieldValidatorの組み合わせテスト
     * BeforeValidator属性とbeforeモードのFieldValidatorが両方適用される
     */
    public function testCombinedValidation(): void
    {
        // 最初のバリデーション（3文字以上）は通過するが、2番目のバリデーション（6文字以上）で失敗
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('6文字以上必要です');
        $this->managerWithBoth->processBeforeValidation('abcde');
    }

    /**
     * 属性とFieldValidatorの組み合わせテスト（成功ケース）
     * BeforeValidator属性とbeforeモードのFieldValidatorの両方を通過
     */
    public function testCombinedValidationSuccess(): void
    {
        $result = $this->managerWithBoth->processBeforeValidation('abcdef');
        $this->assertEquals('abcdef', $result);

        $result = $this->managerWithBoth->processAfterValidation($result);
        $this->assertEquals('Abcdef', $result);
    }
}
