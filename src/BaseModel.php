<?php

declare(strict_types=1);

namespace PhpValueObject;

use PhpValueObject\Config\ConfigModel;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\UninitializedException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Helpers\AssertionHelper;
use PhpValueObject\Support\InputArguments;
use PhpValueObject\Support\PropertyOperator;
use ReflectionClass;
use stdClass;
use TypeError;

abstract class BaseModel
{
    /**
     * @param array<string, mixed> $args
     *
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    final protected function __construct(mixed ...$args)
    {
        $refClass = new ReflectionClass($this);

        $configModel = ConfigModel::factory($refClass);

        // 入力値を取得
        $inputArguments = new InputArguments($refClass, $args);

        AssertionHelper::assertInheritableClass(refClass: $refClass, configModel: $configModel);

        foreach ($refClass->getProperties() as $property) {
            $propertyOperator = new PropertyOperator(refProperty: $property, inputArguments: $inputArguments);

            if (
                AssertionHelper::assertUninitializedPropertyOrSkip(
                    refClass: $refClass,
                    configModel: $configModel,
                    propertyOperator: $propertyOperator,
                )
            ) {
                continue;
            }

            $propertyOperator->checkPropertyType(refClass: $refClass, configModel: $configModel);

            $propertyOperator->validatePropertyValue();

            $propertyOperator->setPropertyValue(vo: $this);
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
