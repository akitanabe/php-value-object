<?php

declare(strict_types=1);

namespace PhpValueObject;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Helpers\AssertionHelper;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Support\FieldValidatorStorage;
use PhpValueObject\Support\FunctionValidatorFactory;
use PhpValueObject\Support\InputData;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\FieldValidationManager;
use ReflectionClass;
use stdClass;
use TypeError;
use PhpValueObject\Exceptions\InvalidPropertyStateException;

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

        // FieldValidatorStorage を生成
        $fieldValidatorStorage = FieldValidatorStorage::createFromClass($refClass);

        foreach ($refClass->getProperties() as $property) {
            $validatorDefinitions = new ValidatorDefinitions();

            $field = FieldsHelper::createField($property);

            $propertyOperator = PropertyOperator::create(
                refProperty: $property,
                inputData: $inputData,
                field: $field,
            );

            // フィールド設定を取得
            $fieldConfig = FieldConfig::factory($property);

            $functionValidatorFactory = FunctionValidatorFactory::createFromStorage(
                validatorStorage: $fieldValidatorStorage,
                property: $property,
            );

            // バリデータの定義に登録
            $validatorDefinitions->registerMultiple(
                $modelConfig,
                $fieldConfig,
                $functionValidatorFactory->createDefinition(),
                $field->getDefinition(),
                $propertyOperator->metadata,
            );

            // フィールドバリデーションマネージャーの作成
            $fieldValidationManager = new FieldValidationManager(
                field: $field,
                functionValidatorFactory: $functionValidatorFactory,
            );

            // すべてのバリデーションを実行
            $validatedPropertyOperator = $fieldValidationManager->processValidation(
                $propertyOperator,
                $validatorDefinitions,
            );

            // 未初期化プロパティが許可されているのならスルー
            if ($validatedPropertyOperator->metadata->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
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
