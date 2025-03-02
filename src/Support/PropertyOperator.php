<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\BaseModel;
use PhpValueObject\Config\ConfigModel;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Fields\Field;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Helpers\TypeHelper;
use PhpValueObject\Validation\Validatable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use TypeError;

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
    ) {
        $this->name = $refProperty->name;

        /** @var BaseField $field */
        $field = AttributeHelper::getAttribute(
            $refProperty,
            BaseField::class,
            ReflectionAttribute::IS_INSTANCEOF,
        )?->newInstance() ?? new Field();

        // プロパティの初期化状態を判定
        $hasFactory = $field->hasFactory();
        $hasInputValue = $inputArguments->hasValue($refProperty->name, $field->alias);
        $hasDefaultValue = $refProperty->hasDefaultValue();

        $this->initializedStatus = match (true) {
            // factoryが存在する場合
            // 入力値が存在する or デフォルト値が存在しない場合のみファクトリー関数を使用
            ($hasFactory && ($hasInputValue || $hasDefaultValue === false)) => PropertyInitializedStatus::BY_FACTORY,

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


        $value = ($this->initializedStatus === PropertyInitializedStatus::BY_DEFAULT)
            ? $refProperty->getDefaultValue()
            : $inputArguments->getValue($refProperty->name, $field->alias);

        $this->value =
            ($this->initializedStatus === PropertyInitializedStatus::BY_FACTORY)
            ? $field->factory($value)
            : $value;

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
    public function checkPropertyType(ReflectionClass $refClass, ConfigModel $configModel): void
    {
        TypeHelper::checkType($refClass, $configModel, $this);
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
