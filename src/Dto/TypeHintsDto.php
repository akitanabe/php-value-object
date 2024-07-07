<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Dto;

use ReflectionIntersectionType;
use ReflectionNamedType;

class TypeHintsDto
{
    public string $typeName;
    public string $valueType;
    public bool $isPrimitive = false;
    public bool $isIntersection = false;

    public function __construct(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
        PropertyDto $propertyDto,
    ) {
        if ($propertyType === null) {
            $this->typeName = 'none';
        } else if ($propertyType instanceof ReflectionIntersectionType) {
            $this->typeName = 'object';
            $this->isIntersection = true;
        } else {
            $this->typeName = $propertyType->getName();
        }

        if (in_array($this->typeName, [
            'int',
            'string',
            'float',
            'bool',
        ], true)) {
            $this->isPrimitive = true;
        }

        $this->valueType = $propertyDto->valueType;
    }
}
