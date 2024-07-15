<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Dto;

use ReflectionIntersectionType;
use ReflectionNamedType;
use Akitanabe\PhpValueObject\Enums\TypeHintsDtoType;


class TypeHintsDto
{
    public TypeHintsDtoType $type;
    public string $valueType;
    public bool $isPrimitive;
    public bool $isIntersection = false;

    public function __construct(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
        PropertyDto $propertyDto,
    ) {
        if ($propertyType === null) {
            $this->type = TypeHintsDtoType::NONE;
        } else if ($propertyType instanceof ReflectionIntersectionType) {
            $this->type = TypeHintsDtoType::OBJECT;
            $this->isIntersection = true;
        } else {
            $this->type = TypeHintsDtoType::tryFrom($propertyType->getName())
                ?? TypeHintsDtoType::OBJECT;
        }

        $this->isPrimitive = match ($this->type->value) {
            'int', 'string', 'float', 'bool' => true,
            default => false,
        };

        $this->valueType = $propertyDto->valueType;
    }
}
