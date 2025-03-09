<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Enums\TypeHintType;
use ReflectionIntersectionType;
use ReflectionNamedType;

class TypeHint
{
    public readonly TypeHintType $type;

    public readonly bool $isPrimitive;

    public readonly bool $isIntersection;

    public function __construct(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
    ) {
        if ($propertyType === null) {
            $this->type = TypeHintType::NONE;
            $this->isIntersection = false;
        } elseif ($propertyType instanceof ReflectionIntersectionType) {
            $this->type = TypeHintType::OBJECT;
            $this->isIntersection = true;
        } else {
            $this->type = TypeHintType::tryFrom($propertyType->getName())
                ?? TypeHintType::OBJECT;
            $this->isIntersection = false;
        }

        $this->isPrimitive = match ($this->type) {
            TypeHintType::STRING => true,
            TypeHintType::INT => true,
            TypeHintType::FLOAT => true,
            TypeHintType::BOOL => true,
            default => false,
        };
    }
}
