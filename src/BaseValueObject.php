<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use ReflectionClass;
use ReflectionAttribute;
use ReflectionProperty;
use TypeError;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Validation\Validatable;
use Akitanabe\PhpValueObject\Helpers\AssertHelper;
use Akitanabe\PhpValueObject\Helpers\ArgumentsHelper;
use Akitanabe\PhpValueObject\Helpers\TypeHelper;
use Akitanabe\PhpValueObject\Dto\PropertyDto;

abstract class BaseValueObject
{
    private Strict $strict;

    /**
     * @param array<string|int,mixed> $args
     * 
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    public function __construct(...$args)
    {
        $refClass = new ReflectionClass($this);

        $strict = new Strict($refClass);
        $this->strict = $strict;

        // finalクラスであることを強制(Attributeが設定されていなければ継承不可)
        AssertHelper::assertInheritableClass($refClass, $strict);

        // 入力値を取得
        $inputArgs = ArgumentsHelper::getInputArgs($refClass, $args);

        foreach ($refClass->getProperties() as $property) {
            $propertyDto = new PropertyDto($this, $property, $inputArgs);

            if (AssertHelper::assertUninitializedPropertyOrSkip(
                $refClass,
                $strict,
                $propertyDto,
            )) {
                continue;
            }

            TypeHelper::checkType(
                $refClass,
                $strict,
                $propertyDto,
            );

            $property->setValue(
                $this,
                $propertyDto->value,
            );

            // プロパティ値バリデーション
            $this->validateProperty($property);
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
    private function validateProperty(ReflectionProperty $refProp): void
    {
        $attributes = $refProp->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);
        $value = $refProp->getValue($this);

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

    /**
     * クローン時にはimmutableにするためオブジェクトはcloneする
     */
    public function __clone()
    {
        foreach (get_object_vars($this) as $prop => $value) {
            if (is_object($value)) {
                $this->{$prop} = clone $value;
            }
        };
    }
}
