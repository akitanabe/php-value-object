<?php

declare(strict_types=1);

namespace PhpValueObject;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Helpers\AssertionHelper;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\FieldValidationManager;
use PhpValueObject\Validators\PrimitiveTypeValidator;
use PhpValueObject\Validators\PropertyInitializedValidator;
use PhpValueObject\Validators\PropertyTypeValidator;
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

            $propertyOperator = PropertyOperator::create(
                refProperty: $property,
                inputData: $inputData,
                field: $field,
            );

            // プロパティの検証を行うバリデータを追加
            $coreValidators = [
                new PropertyInitializedValidator($modelConfig, $fieldConfig, $propertyOperator->metadata),
                new PropertyTypeValidator($modelConfig, $fieldConfig, $propertyOperator->metadata),
                new PrimitiveTypeValidator($propertyOperator->metadata),
            ];

            // フィールドバリデーションマネージャーの作成（コアバリデータも含める）
            $fieldValidationManager = FieldValidationManager::createFromProperty(
                $property,
                $field,
                $fieldValidators,
                $coreValidators,
            );


            // すべてのバリデーションを実行
            $validatedPropertyOperator = $fieldValidationManager->processValidation($propertyOperator);

            // 未初期化プロパティのままならスルー
            if (
                $validatedPropertyOperator->metadata->initializedStatus === PropertyInitializedStatus::UNINITIALIZED
                && $validatedPropertyOperator->value->value === null
            ) {
                continue;
            }

            // PropertyValueオブジェクトからactualValueを取得して設定
            $property->setValue($this, $validatedPropertyOperator->value->value);

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
