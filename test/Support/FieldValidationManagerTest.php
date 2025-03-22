<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Validation\BeforeValidator;
use PhpValueObject\Validation\AfterValidator;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Exceptions\ValidationException;
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
    private FieldValidationManager $manager;
    private ReflectionProperty $property;

    protected function setUp(): void
    {
        $class = new TestClass();
        $this->property = new ReflectionProperty($class, 'name');
        $this->manager = FieldValidationManager::createFromProperty($this->property);
    }

    /**
     * BeforeValidatorのテスト（バリデーション失敗）
     */
    public function testBeforeValidationThrowsException(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3文字以上必要です');
        $this->manager->processBeforeValidation('ab');
    }

    /**
     * BeforeValidatorのテスト（バリデーション成功）
     */
    public function testBeforeValidationSuccess(): void
    {
        $result = $this->manager->processBeforeValidation('abc');
        $this->assertEquals('abc', $result);
    }

    /**
     * AfterValidatorのテスト
     */
    public function testAfterValidation(): void
    {
        $result = $this->manager->processAfterValidation('john');
        $this->assertEquals('John', $result);
    }

    /**
     * バリデーション順序のテスト
     */
    public function testValidationOrder(): void
    {
        $result = $this->manager->processBeforeValidation('john');
        $this->assertEquals('john', $result);

        $result = $this->manager->processAfterValidation($result);
        $this->assertEquals('John', $result);
    }
}
