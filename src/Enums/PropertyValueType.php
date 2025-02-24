<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Enums;

enum PropertyValueType: string
{
    case BOOL = "boolean";
    case INT = "integer";
    case FLOAT = "double";
    case STRING = "string";
    case OBJECT = "object";
    case ARRAY = "array";
    case RESORCE = "resource";
    case CLOSED_RESORCE = "resource (closed)";
    case NULL = "NULL";
    case UNKNOWN_TYPE = "unknown type";
    case UNINITIALIZED = "uninitialized";

    public function shorthand(): string
    {
        return match ($this->value) {
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            'NULL' => 'null',
            default => $this->value,
        };
    }
}
