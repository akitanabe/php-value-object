<?php

namespace Akitanabe\PhpValueObject\Concerns;

use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use ReflectionClass;
use ReflectionProperty;

trait Assert
{
    /**
     * @throws InheritableClassException
     */
    public function assertInheritableClass(ReflectionClass $refClass, Strict $strict): void
    {
        if (
            $refClass->isFinal() === false
            && $strict->inheritableClass->disallow()
        ) {

            throw new InheritableClassException(
                "{$refClass->name} is not allowed to inherit. not allow inheritable class."
            );
        }
    }

    /**
     * @return bool
     * @throws UninitializedException
     */
    public function assertUninitializedPropertyOrSkip(
        ReflectionClass $refClass,
        ReflectionProperty $property,
        Strict $strict,
        array $args,
    ): bool {

        $propertyName = $property->name;

        $initializedProperty = $property->isInitialized($this);
        $inputValueExists = array_key_exists($propertyName, $args);

        // 入力値と初期化済みプロパティの両方が存在しない場合
        if (
            $inputValueExists === false
            && $initializedProperty === false
        ) {
            // 未初期化プロパティが許可されている場合はスキップ
            if ($strict->uninitializedProperty->allow()) {
                return true;
            }

            throw new UninitializedException(
                "{$refClass->name}::\${$propertyName} is not initialized. not allow uninitialized property."
            );
        }

        return false;
    }
}
