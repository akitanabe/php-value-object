<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Dto;

use ReflectionIntersectionType;
use ReflectionNamedType;
use Akitanabe\PhpValueObject\Enums\TypeHintsDtoType;


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
        } else if ($propertyType instanceof ReflectionIntersectionType) {
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
