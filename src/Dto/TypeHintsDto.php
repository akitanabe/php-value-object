<?php

declare(strict_types=1);

namespace PhpValueObject\Dto;

use PhpValueObject\Enums\TypeHintsDtoType;
use ReflectionIntersectionType;
use ReflectionNamedType;

class TypeHintsDto
{
    public readonly TypeHintsDtoType $type;

    public readonly string $valueType;

    public readonly bool $isPrimitive;

    public readonly bool $isIntersection;

    public function __construct(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
    ) {
        if ($propertyType === null) {
            $this->type = TypeHintsDtoType::NONE;
            $this->isIntersection = false;
        } elseif ($propertyType instanceof ReflectionIntersectionType) {
            $this->type = TypeHintsDtoType::OBJECT;
            $this->isIntersection = true;
        } else {
            $this->type = TypeHintsDtoType::tryFrom($propertyType->getName())
                ?? TypeHintsDtoType::OBJECT;
            $this->isIntersection = false;
        }

        $this->isPrimitive = match ($this->type) {
            TypeHintsDtoType::STRING => true,
            TypeHintsDtoType::INT => true,
            TypeHintsDtoType::FLOAT => true,
            TypeHintsDtoType::BOOL => true,
            default => false,
        };
    }
}
