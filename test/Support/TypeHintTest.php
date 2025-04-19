<?php

declare(strict_types=1);

namespace Tests\Support;

use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Support\TypeHint;
use PHPUnit\Framework\TestCase;
use ReflectionIntersectionType;
use ReflectionNamedType;

class TypeHintTest extends TestCase
{
    public function testConstruct(): void
    {
        $typeHint = new TypeHint(TypeHintType::STRING, true, false);

        $this->assertSame(TypeHintType::STRING, $typeHint->type);
        $this->assertTrue($typeHint->isPrimitive);
        $this->assertFalse($typeHint->isIntersection);
    }

    public function testFromReflectionTypeWithNullType(): void
    {
        $typeHint = TypeHint::fromReflectionType(null);

        $this->assertSame(TypeHintType::NONE, $typeHint->type);
        $this->assertFalse($typeHint->isPrimitive);
        $this->assertFalse($typeHint->isIntersection);
    }

    public function testFromReflectionTypeWithIntersectionType(): void
    {
        $reflectionType = $this->createMock(ReflectionIntersectionType::class);

        $typeHint = TypeHint::fromReflectionType($reflectionType);

        $this->assertSame(TypeHintType::OBJECT, $typeHint->type);
        $this->assertFalse($typeHint->isPrimitive);
        $this->assertTrue($typeHint->isIntersection);
    }

    public function testFromReflectionTypeWithPrimitiveType(): void
    {
        $reflectionType = $this->createMock(ReflectionNamedType::class);
        $reflectionType->method('getName')->willReturn('int');

        $typeHint = TypeHint::fromReflectionType($reflectionType);

        $this->assertSame(TypeHintType::INT, $typeHint->type);
        $this->assertTrue($typeHint->isPrimitive);
        $this->assertFalse($typeHint->isIntersection);
    }

    public function testFromReflectionTypeWithObjectType(): void
    {
        $reflectionType = $this->createMock(ReflectionNamedType::class);
        $reflectionType->method('getName')->willReturn('stdClass');

        $typeHint = TypeHint::fromReflectionType($reflectionType);

        $this->assertSame(TypeHintType::OBJECT, $typeHint->type);
        $this->assertFalse($typeHint->isPrimitive);
        $this->assertFalse($typeHint->isIntersection);
    }
}
