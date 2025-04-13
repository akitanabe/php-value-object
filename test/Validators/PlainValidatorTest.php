<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Validators\PlainValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PlainValidatorTest extends TestCase
{
    /**
     * PLAINモードを返すことを確認
     */
    #[Test]
    public function testGetModeReturnsPlainMode(): void
    {
        $validator = new PlainValidator(fn($value) => $value);
        $this->assertEquals(ValidatorMode::PLAIN, $validator->getMode());
    }

    /**
     * バリデーション処理が正しく実行されることを確認
     */
    #[Test]
    public function testValidateExecutesValidatorFunction(): void
    {
        $called = false;
        $validator = new PlainValidator(function ($value) use (&$called) {
            $called = true;
            return $value . '_validated';
        });

        $result = $validator->validate('test');

        $this->assertTrue($called);
        $this->assertEquals('test_validated', $result);
    }
}
