<?php

declare(strict_types=1);

namespace PhpValueObject;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\UninitializedException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Helpers\AssertionHelper;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Support\InputArguments;
use PhpValueObject\Support\PropertyOperator;
use ReflectionClass;
use stdClass;
use TypeError;

abstract class BaseModel
{
    /**
     * @param array<string|int, mixed> $args
     *
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    final protected function __construct(mixed ...$args)
    {
        $refClass = new ReflectionClass($this);

        $modelConfig = ModelConfig::factory($refClass);

        // 入力値を取得
        $inputArguments = new InputArguments($args);

        AssertionHelper::assertInheritableClass(refClass: $refClass, modelConfig: $modelConfig);

        foreach ($refClass->getProperties() as $property) {

            $field = FieldsHelper::createField($property);
            $fieldConfig = FieldConfig::factory($property);

            $propertyOperator = new PropertyOperator(
                refProperty: $property,
                inputArguments: $inputArguments,
                field: $field,
            );

            // 未初期化プロパティの場合はスキップ
            if (
                AssertionHelper::assertUninitializedPropertyOrSkip(
                    refClass: $refClass,
                    modelConfig: $modelConfig,
                    fieldConfig: $fieldConfig,
                    propertyOperator: $propertyOperator,
                )
            ) {
                continue;
            }

            // 許可されていない型を検証
            AssertionHelper::assertDisallowPropertyType(
                refClass: $refClass,
                modelConfig: $modelConfig,
                fieldConfig: $fieldConfig,
                propertyOperator: $propertyOperator,
            );

            $propertyOperator->validatePropertyValue();

            // 入力前にプリミティブ型のチェック
            AssertionHelper::assertPrimitiveType(refClass: $refClass, propertyOperator: $propertyOperator,);

            $propertyOperator->setPropertyValue(model: $this);
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
