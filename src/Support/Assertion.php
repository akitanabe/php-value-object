<?php

namespace Akitanabe\PhpValueObject\Support;

use ReflectionClass;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Dto\PropertyDto;

class Assertion
{
    /**
     * @template T of object
     * @param ReflectionClass<T> $refClass
     * @param Strict $strict
     */
    public function __construct(
        private ReflectionClass $refClass,
        private Strict $strict,
    ) {
    }


    /** 
     * @return void
     * 
     * @throws InheritableClassException
     */
    public function assertInheritableClass(): void
    {
        if (
            $this->refClass->isFinal() === false
            && $this->strict->inheritableClass->disallow()
        ) {

            throw new InheritableClassException(
                "{$this->refClass->name} is not allowed to inherit. not allow inheritable class."
            );
        }
    }

    /**
     * @param PropertyDto $propertyDto
     * 
     * @return bool
     * @throws UninitializedException
     */
    public function assertUninitializedPropertyOrSkip(
        PropertyDto $propertyDto,
    ): bool {


        // プロパティが未初期化の場合
        if ($propertyDto->isUninitialized()) {
            // 未初期化プロパティが許可されている場合はスキップ
            if ($this->strict->uninitializedProperty->allow()) {
                return true;
            }

            throw new UninitializedException(
                "{$this->refClass->name}::\${$propertyDto->name} is not initialized. not allow uninitialized property."
            );
        }

        return false;
    }
}
