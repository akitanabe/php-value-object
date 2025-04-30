<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\BeforeValidator;
use PhpValueObject\Validators\ValidatorMode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * BeforeValidatorのテストクラス
 *
 * BeforeValidatorは FunctionalValidator を継承し、
 * BEFORE モードと callable を提供する
 */
#[CoversClass(BeforeValidator::class)]
class BeforeValidatorTest extends TestCase
{
    /**
     * getModeがValidatorMode::BEFOREを返すことを確認
     */
    #[Test]
    public function shouldReturnCorrectMode(): void
    {
        // Arrange
        $callable = fn($value) => $value;
        $validator = new BeforeValidator($callable);

        // Act
        $mode = $validator->getMode();

        // Assert
        $this->assertSame(ValidatorMode::BEFORE, $mode);
    }

    /**
     * getCallableがコンストラクタで渡されたcallableを返すことを確認
     */
    #[Test]
    public function shouldReturnCallablePassedInConstructor(): void
    {
        // Arrange
        $callable = fn($value) => $value . '_before';
        $validator = new BeforeValidator($callable);

        // Act
        $returnedCallable = $validator->resolveValidator();

        // Assert
        $this->assertSame($callable, $returnedCallable);
        // 実際に呼び出して確認
        $this->assertEquals('test_before', $returnedCallable('test'));
    }
}
