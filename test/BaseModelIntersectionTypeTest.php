<?php

declare(strict_types=1);

namespace PhSculptis\Test;

use PhSculptis\BaseModel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TypeError;

interface ITypeA {}

class TypeB implements ITypeA
{
    public const TYPE = 'B';
}

class TypeC
{
    public const TYPE = 'C';
}

final class IntersectionTypeModel extends BaseModel
{
    public (ITypeA&TypeB)|string $AandBorString;

    public bool|float $floatOrBool;
}

class BaseModelIntersectionTypeTest extends TestCase
{
    #[Test]
    public function intersectionTypeObject(): void
    {
        $intersectionTypeModel = IntersectionTypeModel::fromArray(
            [
                'AandBorString' => new TypeB(),
                'floatOrBool' => false,
            ],
        );

        $this->assertSame(TypeB::class, $intersectionTypeModel->AandBorString::class);
        $this->assertSame(false, $intersectionTypeModel->floatOrBool);

        $intersectionTypeModel = IntersectionTypeModel::fromArray(
            [
                'AandBorString' => 'string',
                'floatOrBool' => 0.01,
            ],
        );

        $this->assertSame('string', $intersectionTypeModel->AandBorString);
        $this->assertSame(0.01, $intersectionTypeModel->floatOrBool);
    }

    /**
     * @return array<array{AandBorString:mixed,floatOrBool:mixed}[]>
     */
    public static function intersectionProperyWithInvalidTypeProvider(): array
    {
        return [
            [
                [
                    'AandBorString' => new TypeC(),
                    'floatOrBool' => 0.01,
                ],
            ],
            [
                [
                    'AandBorString' => 1,
                    'floatOrBool' => false,
                ],
            ],
            [
                [
                    'AandBorString' => 'string',
                    'floatOrBool' => '0.01',
                ],
            ],
            [
                [
                    'AandBorString' => new TypeB(),
                    'floatOrBool' => 1,
                ],
            ],
        ];
    }

    /**
     * @param array{AandBorString:mixed,floatOrBool:mixed} $args
     */
    #[Test]
    #[DataProvider('intersectionProperyWithInvalidTypeProvider')]
    public function interserctionProperyWithInvalidType(array $args): void
    {
        $this->expectException(TypeError::class);
        IntersectionTypeModel::fromArray($args);
    }
}
