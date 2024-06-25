<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Akitanabe\PhpValueObject\BaseValueObject;

class PrimitiveTestValue extends BaseValueObject
{
    public readonly string $stringVal;
    public readonly int $intVal;
    public readonly float $floatVal;
    public readonly bool $boolVal;
}

class UnionTestValue extends BaseValueObject
{
    public readonly string|int $stringOrInt;
    public readonly float|int $floatOrInt;
}

class BaseValueObjectPrimitiveTest extends TestCase
{
    #[Test]
    public function primitivePropetry()
    {
        $scalarValue = new PrimitiveTestValue(
            stringVal: "string",
            intVal: 123,
            floatVal: 0.01,
            boolVal: true,
        );

        $this->assertSame("string", $scalarValue->stringVal);
        $this->assertSame(123, $scalarValue->intVal);
        $this->assertSame(0.01, $scalarValue->floatVal);
        $this->assertSame(true, $scalarValue->boolVal);
    }

    public static function primitiveProperyWithInvalidTypeProvider(): array
    {
        return [
            [
                [
                    "stringVal" => 123,
                    "intVal" => 123,
                    "floatVal" => 0.01,
                    "boolVal" => true
                ],
            ],
            [
                [
                    "stringVal" => "string",
                    "intVal" => "123",
                    "floatVal" => 0.01,
                    "boolVal" => true
                ],
            ],
            [
                [
                    "stringVal" => "string",
                    "intVal" => 123,
                    "floatVal" => "0.01",
                    "boolVal" => true
                ],
            ],
            [
                [
                    "stringVal" => "string",
                    "intVal" => 123,
                    "floatVal" => 0.01,
                    "boolVal" => 1
                ],
            ],
        ];
    }

    #[Test]
    #[DataProvider("primitiveProperyWithInvalidTypeProvider")]
    public function primitiveProperyWithInvalidType(array $args)
    {
        $this->expectException(TypeError::class);
        new PrimitiveTestValue(...$args);
    }

    #[Test]
    public function unionPropery()
    {
        $unionValue = new UnionTestValue(
            stringOrInt: "string",
            floatOrInt: 0.01,
        );

        $this->assertSame("string", $unionValue->stringOrInt);
        $this->assertSame(0.01, $unionValue->floatOrInt);

        $unionValue = new UnionTestValue(
            stringOrInt: 123,
            floatOrInt: 1,
        );

        $this->assertSame(123, $unionValue->stringOrInt);
        $this->assertSame(1, $unionValue->floatOrInt);
    }
}
