<?php

declare(strict_types=1);

namespace PhpValueObject;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Helpers\AssertionHelper;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\FieldValidationManager;
use ReflectionClass;
use stdClass;
use TypeError;

abstract class BaseModel
{
    /**
     * @param array<string|int, mixed> $data
     *
     * @throws InheritableClassException|InvalidPropertyStateException|ValidationException|TypeError
     */
    final protected function __construct(array $data = [])
    {
        $refClass = new ReflectionClass($this);

        $modelConfig = ModelConfig::factory($refClass);

        // 入力値を取得
        $inputData = new InputData($data);

        AssertionHelper::assertInheritableClass(refClass: $refClass, modelConfig: $modelConfig);

        $fieldValidators = FieldsHelper::getFieldValidators($refClass);

        foreach ($refClass->getProperties() as $property) {

            $field = FieldsHelper::createField($property);
            $fieldConfig = FieldConfig::factory($property);
            $fieldValidationManager = FieldValidationManager::createFromProperty($property, $field, $fieldValidators);

            $propertyOperator = PropertyOperator::create(
                refProperty: $property,
                inputData: $inputData,
                field: $field,
            );

            // プロパティ状態の検証
            if (
                AssertionHelper::assertInvalidPropertyStateOrSkip(
                    modelConfig: $modelConfig,
                    fieldConfig: $fieldConfig,
                    propertyOperator: $propertyOperator,
                )
            ) {
                continue;
            }

            $validatedPropertyOperator = $fieldValidationManager->processValidation($propertyOperator);

            // プリミティブ型のチェック
            AssertionHelper::assertPrimitiveType(propertyOperator: $validatedPropertyOperator);

            $property->setValue($this, $validatedPropertyOperator->value);
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
