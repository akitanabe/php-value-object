<?php

namespace PhpValueObject\Helpers;

use PhpValueObject\Config\ConfigClass;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\UninitializedException;
use PhpValueObject\Support\PropertyOperator;
use ReflectionClass;

class AssertionHelper
{
    /**
     * @template T of object
     * @param ReflectionClass<T> $refClass
     *
     * @throws InheritableClassException
     */
    public static function assertInheritableClass(ReflectionClass $refClass, ConfigClass $configClass): void
    {
        if (
            $refClass->isFinal() === false
            && $configClass->inheritableClass->disallow()
        ) {

            throw new InheritableClassException(
                "{$refClass->name} is not allowed to inherit. not allow inheritable class.",
            );
        }
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $refClass
     *
     * @throws UninitializedException
     */
    public static function assertUninitializedPropertyOrSkip(
        ReflectionClass $refClass,
        ConfigClass $configClass,
        PropertyOperator $propertyOperator,
    ): bool {

        // プロパティが未初期化の場合
        if ($propertyOperator->isUninitializedProperty()) {
            // 未初期化プロパティが許可されている場合はスキップ
            if ($configClass->uninitializedProperty->allow()) {
                return true;
            }

            throw new UninitializedException(
                "{$refClass->name}::\${$propertyOperator->name} is not initialized. not allow uninitialized property.",
            );
        }

        return false;
    }
}
