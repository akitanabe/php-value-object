<?php

declare(strict_types=1);

namespace PhpValueObject\Enums;

enum TypeHintsDtoType: string
{
    case NONE = 'none';
    case MIXED = 'mixed';
    case OBJECT = 'object';
    case INT = 'int';
    case STRING = 'string';
    case FLOAT = 'float';
    case BOOL = 'bool';
}
