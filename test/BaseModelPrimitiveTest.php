<?php

declare(strict_types=1);

use PhpValueObject\BaseModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PrimitiveTestModel extends BaseModel
{
    public readonly string $stringVal;

    public readonly int $intVal;

    public readonly float $floatVal;

    public readonly bool $boolVal;
}

final class UnionTestModel extends BaseModel
{
    public readonly string|int $stringOrInt;

    public readonly float|int $floatOrInt;
}

class BaseModelPrimitiveTest extends TestCase
{
    #[Test]
    public function primitivePropetry(): void
    {
        $scalarModel = PrimitiveTestModel::fromArray(
            [
                'stringVal' => 'string',
                'intVal' => 123,
                'floatVal' => 0.01,
                'boolVal' => true,
            ],
        );

        $this->assertSame('string', $scalarModel->stringVal);
        $this->assertSame(123, $scalarModel->intVal);
        $this->assertSame(0.01, $scalarModel->floatVal);
        $this->assertSame(true, $scalarModel->boolVal);
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
        PrimitiveTestModel::fromArray($args);
    }

    #[Test]
    public function unionPropery(): void
    {
        $unionModel = UnionTestModel::fromArray([
            'stringOrInt' => 'string',
            'floatOrInt' => 0.01,
        ],);

        $this->assertSame('string', $unionModel->stringOrInt);
        $this->assertSame(0.01, $unionModel->floatOrInt);

        $unionModel = UnionTestModel::fromArray([
            'stringOrInt' => 123,
            'floatOrInt' => 1,
        ],);

        $this->assertSame(123, $unionModel->stringOrInt);
        $this->assertSame(1, $unionModel->floatOrInt);
    }
}
