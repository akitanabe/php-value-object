<?php

namespace Akitanabe\PhpValueObject\Concerns;

use ReflectionClass;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Dto\PropertyDto;

trait Assert
{
    /**
     * @throws InheritableClassException
     */
    private function assertInheritableClass(ReflectionClass $refClass, Strict $strict): void
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
    private function assertUninitializedPropertyOrSkip(
        ReflectionClass $refClass,
        Strict $strict,
        PropertyDto $propertyDto,
    ): bool {


        // プロパティが未初期化の場合
        if ($propertyDto->isUninitialized()) {
            // 未初期化プロパティが許可されている場合はスキップ
            if ($strict->uninitializedProperty->allow()) {
                return true;
            }

            throw new UninitializedException(
                "{$refClass->name}::\${$propertyDto->name} is not initialized. not allow uninitialized property."
            );
        }

        return false;
    }
}
