<?php

declare(strict_types=1);

namespace PhSculptis\Helpers;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionMethod;
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

    /**
     * @template T of object
     * @template C of object
     * @param ReflectionClass<C>|ReflectionProperty|ReflectionMethod $reflection
     * @param class-string<T> $attributeName
     * @return T[]
     */
    public static function getAttributeInstances(
        ReflectionClass|ReflectionProperty|ReflectionMethod $reflection,
        string $attributeName,
        int $flags = 0,
    ): array {
        return array_map(
            fn(ReflectionAttribute $attr) => $attr->newInstance(),
            $reflection->getAttributes($attributeName, $flags),
        );
    }
}
