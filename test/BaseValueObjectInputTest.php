<?php

declare(strict_types=1);

use Akitanabe\PhpValueObject\BaseValueObject;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BasicInputTestValue extends BaseValueObject
{
    public string $string;

    public bool $bool;

    public int $int;

    public float $float;
}

final class DefaultOverrideContructorValue extends BaseValueObject
{
    public string $string = 'string';

    public bool $bool = true;

    public int $int = 1;

    public float $float = 0.1;
}

class BaseValueObjectInputTest extends TestCase
{
    // 基本入力テスト
    #[Test]
    public function basicInput(): void
    {
        $value = BasicInputTestValue::fromArray(
            [
                'string' => 'string',
                'bool' => true,
                'int' => 1,
                'float' => 0.1,
            ],
        );

        $this->assertSame('string', $value->string);
        $this->assertSame(true, $value->bool);
        $this->assertSame(1, $value->int);
        $this->assertSame(0.1, $value->float);
    }

    // デフォルト値をオーバーライドされた場合、されていない場合のテスト
    #[Test]
    public function defaultOverrideConstructor(): void
    {
        $value = DefaultOverrideContructorValue::fromArray([
            'string' => 'default',
            'bool' => false,
        ],);

        $this->assertSame('default', $value->string);
        $this->assertSame(false, $value->bool);
        $this->assertSame(1, $value->int);
        $this->assertSame(0.1, $value->float);
    }
}
