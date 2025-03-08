<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Enums\TypeHintsType;
use ReflectionIntersectionType;
use ReflectionNamedType;

class TypeHints
{
    public readonly TypeHintsType $type;

    public readonly bool $isPrimitive;

    public readonly bool $isIntersection;

    public function __construct(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
    ) {
        if ($propertyType === null) {
            $this->type = TypeHintsType::NONE;
            $this->isIntersection = false;
        } elseif ($propertyType instanceof ReflectionIntersectionType) {
            $this->type = TypeHintsType::OBJECT;
            $this->isIntersection = true;
        } else {
            $this->type = TypeHintsType::tryFrom($propertyType->getName())
                ?? TypeHintsType::OBJECT;
            $this->isIntersection = false;
        }

        $this->isPrimitive = match ($this->type) {
            TypeHintsType::STRING => true,
            TypeHintsType::INT => true,
            TypeHintsType::FLOAT => true,
            TypeHintsType::BOOL => true,
            default => false,
        };
    }
}
