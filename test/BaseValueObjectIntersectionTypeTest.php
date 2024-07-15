<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Akitanabe\PhpValueObject\BaseValueObject;


interface ITypeA
{
}

class TypeB implements ITypeA
{
    public const TYPE = 'B';
}

class TypeC
{
    public const TYPE = 'C';
}

final class IntersectionTypeValue extends BaseValueObject
{
    public (ITypeA&TypeB)|string $AandBorString;
    public bool|float $floatOrBool;
}

class BaseValueObjectIntersectionTypeTest extends TestCase
{
    #[Test]
    public function intersectionTypeObject(): void
    {
        $intersectionTypeValue = new IntersectionTypeValue(
            AandBorString: new TypeB(),
            floatOrBool: false,
        );

        $this->assertSame(TypeB::class, $intersectionTypeValue->AandBorString::class);
        $this->assertSame(false, $intersectionTypeValue->floatOrBool);

        $intersectionTypeValue = new IntersectionTypeValue(
            AandBorString: "string",
            floatOrBool: 0.01,
        );

        $this->assertSame("string", $intersectionTypeValue->AandBorString);
        $this->assertSame(0.01, $intersectionTypeValue->floatOrBool);
    }

    /**
     * @return array<array{AandBorString:mixed,floatOrBool:mixed}[]>
     */
    public static function intersectionProperyWithInvalidTypeProvider(): array
    {
        return [
            [
                [
                    "AandBorString" => new TypeC(),
                    "floatOrBool" => 0.01,
                ]
            ],
            [
                [
                    "AandBorString" => 1,
                    "floatOrBool" => false,
                ]
            ],
            [
                [
                    "AandBorString" => "string",
                    "floatOrBool" => "0.01",
                ]
            ],
            [
                [
                    "AandBorString" => new TypeB(),
                    "floatOrBool" => 1,
                ]
            ],
        ];
    }

    /**
     * @param array{AandBorString:mixed,floatOrBool:mixed} $args
     * @return void
     */
    #[Test]
    #[DataProvider("intersectionProperyWithInvalidTypeProvider")]
    public function interserctionProperyWithInvalidType(array $args): void
    {
        $this->expectException(TypeError::class);
        new IntersectionTypeValue(...$args);
    }
}
