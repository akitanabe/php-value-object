<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Validators\PlainValidator;
use PhpValueObject\Validators\ValidatorMode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PlainValidatorのテストクラス
 *
 * PlainValidatorは FunctionalValidator を継承し、
 * PLAIN モードと callable を提供する
 */
#[CoversClass(PlainValidator::class)]
class PlainValidatorTest extends TestCase
{
    /**
     * getModeがValidatorMode::PLAINを返すことを確認
     */
    #[Test]
    public function shouldReturnCorrectMode(): void
    {
        // Arrange
        $callable = fn($value) => $value;
        $validator = new PlainValidator($callable);

        // Act
        $mode = $validator->getMode();

        // Assert
        $this->assertSame(ValidatorMode::PLAIN, $mode);
    }

    /**
     * getCallableがコンストラクタで渡されたcallableを返すことを確認
     */
    #[Test]
    public function shouldReturnCallablePassedInConstructor(): void
    {
        // Arrange
        $callable = fn($value) => $value . '_plain';
        $validator = new PlainValidator($callable);

        // Act
        $returnedCallable = $validator->resolveValidator();

        // Assert
        $this->assertSame($callable, $returnedCallable);
        // 実際に呼び出して確認
        $this->assertEquals('test_plain', $returnedCallable('test'));
    }
}
