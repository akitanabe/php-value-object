<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Helpers;

use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Dto\PropertyDto;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Support\InputArguments;
use Akitanabe\PhpValueObject\Validation\Validatable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

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
            $propertyDto = new PropertyDto($this->vo, $property, $this->inputArguments);

            if (
                AssertionHelper::assertUninitializedPropertyOrSkip(
                    refClass: $this->refClass,
                    strict: $this->strict,
                    propertyDto: $propertyDto,
                )
            ) {
                continue;
            }

            TypeHelper::checkType($this->refClass, $this->strict, $propertyDto);

            $property->setValue($this->vo, $propertyDto->value);

            // プロパティ値バリデーション
            $this->validateProperty($property, $propertyDto->value);
        }
    }

    /**
     * プロパティに設定されているAttributeからバリデーションを実行
     *
     * @throws ValidationException
     */
    private function validateProperty(ReflectionProperty $refProp, mixed $value): void
    {
        $attributes = $refProp->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance->validate($value) === false) {
                throw new ValidationException($attributeInstance, $refProp);
            }
        }
    }
}
