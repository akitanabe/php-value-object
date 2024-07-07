<?php

namespace Akitanabe\PhpValueObject\Concerns;

use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Dto\PropertyDto;
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
        Strict $strict,
        PropertyDto $propertyDto,
    ): bool {


        // 入力値と初期化済みプロパティの両方が存在しない場合
        if (
            $propertyDto->isInputValue === false
            && $propertyDto->isInitialized === false
        ) {
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
