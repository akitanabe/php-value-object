<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Helpers;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionFunctionAbstract;
use ReflectionObject;
use ReflectionParameter;

final class AttributeHelper
{
    /**
     * @template T of object
     * @param ReflectionClass<T>|ReflectionClassConstant|ReflectionFunctionAbstract|ReflectionObject|ReflectionParameter $reflection
     * @param class-string<T> $attributeName
     *
     * @return ?ReflectionAttribute<T>
     */
    public static function getAttribute(
        ReflectionClass|ReflectionClassConstant|ReflectionFunctionAbstract|ReflectionObject|ReflectionParameter $reflection,
        string $attributeName,
    ): ?ReflectionAttribute {
        return $reflection->getAttributes($attributeName)[0] ?? null;
    }
}
