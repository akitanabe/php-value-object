<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Helpers;

use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Support\InputArguments;
use Akitanabe\PhpValueObject\Support\PropertyOperator;
use ReflectionClass;

class PropertyHelper
{
    /**
     * @template T of object
     * @param ReflectionClass<T> $refClass
     */
    public function __construct(
        private BaseValueObject $vo,
        private ReflectionClass $refClass,
        private Strict $strict,
        private InputArguments $inputArguments,
    ) {
    }

    public function execute(): void
    {
        AssertionHelper::assertInheritableClass(refClass: $this->refClass, strict: $this->strict);

        foreach ($this->refClass->getProperties() as $property) {
            $propertyOperator = new PropertyOperator($this->vo, $property, $this->inputArguments);

            if (
                AssertionHelper::assertUninitializedPropertyOrSkip(
                    refClass: $this->refClass,
                    strict: $this->strict,
                    propertyOperator: $propertyOperator,
                )
            ) {
                continue;
            }

            $propertyOperator->checkPropertyType(refClass: $this->refClass, strict: $this->strict);

            $propertyOperator->setPropertyValue();
            $propertyOperator->validatePropertyValue();

        }
    }
}
