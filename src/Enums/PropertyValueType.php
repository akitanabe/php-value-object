<?php

declare(strict_types=1);

namespace PhpValueObject\Enums;

enum PropertyValueType: string
{
    case BOOL = 'boolean';
    case INT = 'integer';
    case FLOAT = 'double';
    case STRING = 'string';
    case OBJECT = 'object';
    case ARRAY = 'array';
    case RESORCE = 'resource';
    case CLOSED_RESORCE = 'resource (closed)';
    case NULL = 'NULL';
    case UNKNOWN_TYPE = 'unknown type';

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

    /**
     * @param string $shorthand 短縮形の型名から PropertyValueType を生成
     */
    public static function fromShorthand(string $shorthand): self
    {
        return match ($shorthand) {
            'bool' => self::BOOL,
            'int' => self::INT,
            'float' => self::FLOAT,
            'null' => self::NULL,
            default => self::from($shorthand),
        };
    }
}
