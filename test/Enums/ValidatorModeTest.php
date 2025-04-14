<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Enums;

use PhpValueObject\Enums\ValidatorMode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

/**
 * ValidatorModeのテストクラス
 */
#[CoversClass(ValidatorMode::class)]
class ValidatorModeTest extends TestCase
{
    /**
     * 各バリデーションモードの値が正しく設定されていることを確認
     */
    #[Test]
    public function testValidatorModeValues(): void
    {
        $this->assertSame('plain', ValidatorMode::PLAIN->value);
        $this->assertSame('wrap', ValidatorMode::WRAP->value);
        $this->assertSame('before', ValidatorMode::BEFORE->value);
        $this->assertSame('internal', ValidatorMode::INTERNAL->value);
        $this->assertSame('after', ValidatorMode::AFTER->value);
    }

    /**
     * getPriority()メソッドが正しい優先順位を返すことを確認
     */
    #[Test]
    public function testGetPriorityReturnsCorrectValues(): void
    {
        $this->assertSame(0, ValidatorMode::PLAIN->getPriority());
        $this->assertSame(1, ValidatorMode::WRAP->getPriority());
        $this->assertSame(1, ValidatorMode::BEFORE->getPriority());
        $this->assertSame(2, ValidatorMode::INTERNAL->getPriority());
        $this->assertSame(3, ValidatorMode::AFTER->getPriority());
    }

    /**
     * WRAPとBEFOREが同じ優先順位を持つことを確認
     */
    #[Test]
    public function testWrapAndBeforeHaveSamePriority(): void
    {
        $this->assertSame(ValidatorMode::WRAP->getPriority(), ValidatorMode::BEFORE->getPriority());
    }
}
