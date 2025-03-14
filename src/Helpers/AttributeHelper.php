<?php

declare(strict_types=1);

namespace PhpValueObject\Helpers;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionObject;
use ReflectionParameter;
use ReflectionProperty;

final class AttributeHelper
{
    /**
     * @template C of object
     * @template T of object
     * @param ReflectionClass<C>|ReflectionClassConstant|ReflectionFunctionAbstract|ReflectionObject|ReflectionParameter|ReflectionProperty $reflection
     * @param class-string<T> $attributeName
     *
     * @return ?ReflectionAttribute<T>
     */
    public static function getAttribute(
        ReflectionClass|ReflectionClassConstant|ReflectionFunctionAbstract|ReflectionObject|ReflectionParameter|ReflectionProperty $reflection,
        string $attributeName,
        int $flags = 0,
    ): ?ReflectionAttribute {
        return $reflection->getAttributes($attributeName, $flags)[0] ?? null;
    }
}
