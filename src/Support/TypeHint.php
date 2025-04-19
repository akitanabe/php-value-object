<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Enums\TypeHintType;
use ReflectionIntersectionType;
use ReflectionNamedType;

class TypeHint
{
    public function __construct(
        public readonly TypeHintType $type,
        public readonly bool $isPrimitive,
        public readonly bool $isIntersection,
    ) {}

    public static function fromReflectionType(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
    ): self {
        if ($propertyType === null) {
            return new self(TypeHintType::NONE, false, false);
        }

        if ($propertyType instanceof ReflectionIntersectionType) {
            return new self(TypeHintType::OBJECT, false, true);
        }

        $type = TypeHintType::tryFrom($propertyType->getName())
            ?? TypeHintType::OBJECT;

        $isPrimitive = match ($type) {
            TypeHintType::STRING => true,
            TypeHintType::INT => true,
            TypeHintType::FLOAT => true,
            TypeHintType::BOOL => true,
            default => false,
        };

        return new self($type, $isPrimitive, false);
    }
}
