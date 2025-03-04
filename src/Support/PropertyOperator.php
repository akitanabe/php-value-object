<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\BaseModel;
use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Helpers\TypeHelper;
use PhpValueObject\Validation\Validatable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use TypeError;
use UnexpectedValueException;

final class PropertyOperator
{
    public readonly string $name;

    public readonly PropertyInitializedStatus $initializedStatus;

    /**
     * @var (ReflectionNamedType|ReflectionIntersectionType|null)[]
     */
    public readonly array $types;

    public readonly mixed $value;

    public readonly PropertyValueType $valueType;

    public function __construct(
        protected ReflectionProperty $refProperty,
        InputArguments $inputArguments,
        BaseField $field,
    ) {
        $this->name = $refProperty->name;

        // プロパティの初期化状態を判定
        $hasInputValue = $inputArguments->hasValue($refProperty->name, $field->alias);
        $hasDefaultFactory = $field->hasDefaultFactory();
        $hasDefaultValue = $refProperty->hasDefaultValue();

        // デフォルトファクトリーとデフォルト値が両方存在する場合は例外を投げる
        if ($hasDefaultFactory && $hasDefaultValue) {
            throw new UnexpectedValueException("{$refProperty->name} has both default factory and default value.");
        }

        $this->initializedStatus = match (true) {
            // デフォルトファクトリが存在する場合
            $hasDefaultFactory => PropertyInitializedStatus::BY_FACTORY,

            // 外部入力が存在
            $hasInputValue => PropertyInitializedStatus::BY_INPUT,

            // デフォルト値が存在
            $hasDefaultValue => PropertyInitializedStatus::BY_DEFAULT,

            // 未初期化
            default => PropertyInitializedStatus::UNINITIALIZED,
        };

        // 入力値と初期化済みプロパティの両方が存在しない場合
        if ($this->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            $this->value = null;
            $this->valueType = PropertyValueType::NULL;
            return;
        }

        $propertyType = $refProperty->getType();

        // PHPStanのチェックで継承元のReflectionType|nullが入ってくるので無視する(設定にある？)
        // @phpstan-ignore assign.propertyType
        $this->types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];

        // プロパティの初期化状態に応じて値を取得
        $this->value = match (true) {
            $this->initializedStatus === PropertyInitializedStatus::BY_FACTORY => $field->defaultFactory(
                $inputArguments->inputs,
            ),
            $this->initializedStatus === PropertyInitializedStatus::BY_INPUT => $inputArguments->getValue(
                $refProperty->name,
                $field->alias,
            ),
            default => $refProperty->getDefaultValue(),
        };

        $this->valueType = TypeHelper::getValueType($this->value);
    }

    /**
     * プロパティが未初期化状態か
     */
    public function isUninitializedProperty(): bool
    {
        return $this->initializedStatus === PropertyInitializedStatus::UNINITIALIZED;
    }

    /**
     * プロパティの型をチェック
     *
     * @param ReflectionClass<BaseModel> $refClass
     *
     * @throws TypeError
     */
    public function checkPropertyType(
        ReflectionClass $refClass,
        ModelConfig $modelConfig,
        FieldConfig $fieldConfig,
    ): void {
        TypeHelper::checkType($refClass, $modelConfig, $fieldConfig, $this);
    }

    /**
     * プロパティに値を設定
     */
    public function setPropertyValue(BaseModel $model): void
    {
        $this->refProperty->setValue($model, $this->value);
    }

    /**
     * プロパティに設定されているAttributeからバリデーションを実行
     *
     * @throws ValidationException
     */
    public function validatePropertyValue(): void
    {
        $attributes = $this->refProperty->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);

        if (count($attributes) === 0) {
            return;
        }

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance->validate($this->value) === false) {
                throw new ValidationException($attributeInstance, $this->refProperty);
            }
        }
    }
}
