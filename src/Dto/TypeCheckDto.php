<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Dto;

class TypeCheckDto
{
    public function __construct(
        public readonly string $typeName,
        public readonly string $valueType,
        public readonly bool $isPrimivtive = false,
        public readonly bool $isIntersection = false,
    ) {
    }
}
