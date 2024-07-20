<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Helpers;

use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Dto\PropertyDto;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Validation\Validatable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

class PropertyHelper
{
    public function __construct(
        private BaseValueObject $vo,
        private ReflectionClass $refClass,
        private Strict $strict,
        private array $inputArgs,
    ) {
    }

    public function execute()
    {
        foreach ($this->refClass->getProperties() as $property) {
            $propertyDto = new PropertyDto($this->vo, $property, $this->inputArgs);

            if (AssertHelper::assertUninitializedPropertyOrSkip(
                $this->refClass,
                $this->strict,
                $propertyDto,
            )) {
                continue;
            }

            TypeHelper::checkType(
                $this->refClass,
                $this->strict,
                $propertyDto,
            );

            $property->setValue(
                $this->vo,
                $propertyDto->value,
            );

            // プロパティ値バリデーション
            $this->validateProperty($property, $propertyDto->value);
        }
    }

    /**
     * プロパティに設定されているAttributeからバリデーションを実行
     * 
     * @param ReflectionProperty $refProp
     * @return void
     * 
     * @throws ValidationException
     */
    private function validateProperty(ReflectionProperty $refProp, mixed $value): void
    {
        $attributes = $refProp->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance->validate($value) === false) {
                throw new ValidationException(
                    $attributeInstance,
                    $refProp,
                );
            }
        }
    }
}
