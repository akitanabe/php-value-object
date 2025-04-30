<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\AfterValidator;
use PhpValueObject\Validators\FunctionalValidatorMode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * AfterValidatorのテストクラス
 *
 * AfterValidatorは FunctionalValidator を継承し、
 * AFTER モードと callable を提供する
 */
#[CoversClass(AfterValidator::class)]
class AfterValidatorTest extends TestCase
{
    /**
     * getModeがFunctionalValidatorMode::AFTERを返すことを確認
     */
    #[Test]
    public function shouldReturnCorrectMode(): void
    {
        // Arrange
        $callable = fn($value) => $value;
        $validator = new AfterValidator($callable);

        // Act
        $mode = $validator->getMode();

        // Assert
        $this->assertSame(FunctionalValidatorMode::AFTER, $mode);
    }

    /**
     * getCallableがコンストラクタで渡されたcallableを返すことを確認
     */
    #[Test]
    public function shouldReturnCallablePassedInConstructor(): void
    {
        // Arrange
        $callable = fn($value) => $value . '_after';
        $validator = new AfterValidator($callable);

        // Act
        $returnedCallable = $validator->resolveValidator();

        // Assert
        $this->assertSame($callable, $returnedCallable);
        // 実際に呼び出して確認
        $this->assertEquals('test_after', $returnedCallable('test'));
    }
}
