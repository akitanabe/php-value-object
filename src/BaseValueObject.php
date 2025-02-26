<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Helpers\AssertionHelper;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Support\InputArguments;
use Akitanabe\PhpValueObject\Support\PropertyOperator;
use ReflectionClass;
use stdClass;
use TypeError;

abstract class BaseValueObject
{
    /**
     * @param array<string, mixed> $args
     *
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    final protected function __construct(...$args)
    {
        $refClass = new ReflectionClass($this);

        $strict = new Strict($refClass);

        // 入力値を取得
        $inputArguments = new InputArguments($refClass, $args);

        AssertionHelper::assertInheritableClass(refClass: $refClass, strict: $strict);

        foreach ($refClass->getProperties() as $property) {
            $propertyOperator = new PropertyOperator(
                vo: $this,
                refProperty: $property,
                inputArguments: $inputArguments,
            );

            if (
                AssertionHelper::assertUninitializedPropertyOrSkip(
                    refClass: $refClass,
                    strict: $strict,
                    propertyOperator: $propertyOperator,
                )
            ) {
                continue;
            }

            $propertyOperator->checkPropertyType(refClass: $refClass, strict: $strict);

            $propertyOperator->setPropertyValue();
            $propertyOperator->validatePropertyValue();

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
        }
    }

    /**
     * 連想配列からValueObjectを作成する
     *
     * @param array<string, mixed> $array
     */
    final public static function fromArray(array $array = []): static
    {
        return new static(...$array);
    }

    /**
     * オブジェクトからValueObjectを作成する
     */
    final public static function fromObject(object $object = new stdClass()): static
    {
        return self::fromArray(get_object_vars($object));
    }
}
