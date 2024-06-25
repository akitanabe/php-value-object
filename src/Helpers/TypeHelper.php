<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Helpers;

class TypeHelper
{
    /**
     * gettypeの結果をPHPの型名に変換
     * 
     * @param mixed $value
     * 
     * @return string
     * 
     */
    static public function getValueType(mixed $value): string
    {
        $typeName = gettype($value);

        return match ($typeName) {
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => strtolower($typeName),
        };
    }
}
