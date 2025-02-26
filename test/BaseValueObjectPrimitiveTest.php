<?php

declare(strict_types=1);

use PhpValueObject\BaseValueObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrimitiveTestValue extends BaseValueObject
{
    public readonly string $stringVal;

    public readonly int $intVal;

    public readonly float $floatVal;

    public readonly bool $boolVal;
}

final class UnionTestValue extends BaseValueObject
{
    public readonly string|int $stringOrInt;

    public readonly float|int $floatOrInt;
}

class BaseValueObjectPrimitiveTest extends TestCase
{
    #[Test]
    public function primitivePropetry(): void
    {
        $scalarValue = PrimitiveTestValue::fromArray(
            [
                'stringVal' => 'string',
                'intVal' => 123,
                'floatVal' => 0.01,
                'boolVal' => true,
            ],
        );

        $this->assertSame('string', $scalarValue->stringVal);
        $this->assertSame(123, $scalarValue->intVal);
        $this->assertSame(0.01, $scalarValue->floatVal);
        $this->assertSame(true, $scalarValue->boolVal);
    }

    /**
     * @return array<array{stringVal:mixed,intVal:mixed,floatVal:mixed,boolVal:mixed}[]>
     */
    public static function primitiveProperyWithInvalidTypeProvider(): array
    {
        return [
            [
                [
                    'stringVal' => 123,
                    'intVal' => 123,
                    'floatVal' => 0.01,
                    'boolVal' => true,
                ],
            ],
            [
                [
                    'stringVal' => 'string',
                    'intVal' => '123',
                    'floatVal' => 0.01,
                    'boolVal' => true,
                ],
            ],
            [
                [
                    'stringVal' => 'string',
                    'intVal' => 123,
                    'floatVal' => '0.01',
                    'boolVal' => true,
                ],
            ],
            [
                [
                    'stringVal' => 'string',
                    'intVal' => 123,
                    'floatVal' => 0.01,
                    'boolVal' => 1,
                ],
            ],
        ];
    }

    /**
     * @param array{stringVal:mixed,intVal:mixed,floatVal:mixed,boolVal:mixed} $args
     */
    #[Test]
    #[DataProvider('primitiveProperyWithInvalidTypeProvider')]
    public function primitiveProperyWithInvalidType(array $args): void
    {
        $this->expectException(TypeError::class);
        PrimitiveTestValue::fromArray($args);
    }

    #[Test]
    public function unionPropery(): void
    {
        $unionValue = UnionTestValue::fromArray([
            'stringOrInt' => 'string',
            'floatOrInt' => 0.01,
        ], );

        $this->assertSame('string', $unionValue->stringOrInt);
        $this->assertSame(0.01, $unionValue->floatOrInt);

        $unionValue = UnionTestValue::fromArray([
            'stringOrInt' => 123,
            'floatOrInt' => 1,
        ], );

        $this->assertSame(123, $unionValue->stringOrInt);
        $this->assertSame(1, $unionValue->floatOrInt);
    }
}
