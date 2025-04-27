<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PHPUnit\Framework\TestCase;
use PhpValueObject\Validators\IdenticalValidator;
use stdClass;

class IdenticalValidatorTest extends TestCase
{
    public function testValidateReturnsValueAsIs(): void
    {
        $validator = new IdenticalValidator();

        // 文字列のテスト
        $stringValue = 'test string';
        $this->assertSame($stringValue, $validator->validate($stringValue));

        // 数値のテスト
        $numericValue = 123;
        $this->assertSame($numericValue, $validator->validate($numericValue));

        // 配列のテスト
        $arrayValue = ['test', 123];
        $this->assertSame($arrayValue, $validator->validate($arrayValue));

        // オブジェクトのテスト
        $objectValue = new stdClass();
        $this->assertSame($objectValue, $validator->validate($objectValue));

        // nullのテスト
        $this->assertNull($validator->validate(null));
    }
}
