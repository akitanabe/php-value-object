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
        PropertyDto $propertyDto,
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

        $this->isPrimitive = match ($this->type->value) {
            'int', 'string', 'float', 'bool' => true,
            default => false,
        };

        $this->valueType = $propertyDto->valueType;
    }
}
