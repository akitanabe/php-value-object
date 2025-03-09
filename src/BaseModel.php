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
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use ReflectionClass;
use stdClass;
use TypeError;

abstract class BaseModel
{
    /**
     * @param array<string|int, mixed> $data
     *
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    final protected function __construct(array $data = [])
    {
        $refClass = new ReflectionClass($this);

        $modelConfig = ModelConfig::factory($refClass);

        // 入力値を取得
        $inputData = new InputData($data);

        AssertionHelper::assertInheritableClass(refClass: $refClass, modelConfig: $modelConfig);

        foreach ($refClass->getProperties() as $property) {

            $field = FieldsHelper::createField($property);
            $fieldConfig = FieldConfig::factory($property);

            $propertyOperator = new PropertyOperator(
                refProperty: $property,
                inputData: $inputData,
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
            AssertionHelper::assertPrimitiveType(refClass: $refClass, propertyOperator: $propertyOperator);

            $property->setValue($this, $propertyOperator->value);
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
     * 連想配列からModelを作成する
     *
     * @param array<string|int, mixed> $data
     */
    final public static function fromArray(array $data = []): static
    {
        return new static($data);
    }

    /**
     * オブジェクトからModelを作成する
     */
    final public static function fromObject(object $object = new stdClass()): static
    {
        return self::fromArray(data: get_object_vars($object));
    }
}
