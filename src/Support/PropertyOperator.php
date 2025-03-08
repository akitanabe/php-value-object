<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\BaseModel;
use PhpValueObject\Dto\TypeHintsDto;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Helpers\PropertyHelper;
use PhpValueObject\Validation\Validatable;
use ReflectionAttribute;
use ReflectionProperty;
use TypeError;

final class PropertyOperator
{
    public readonly string $name;

    public readonly PropertyInitializedStatus $initializedStatus;

    public readonly mixed $value;

    public readonly PropertyValueType $valueType;

    /** @var TypeHintsDto[] */
    public readonly array $typeHints;

    public function __construct(
        protected ReflectionProperty $refProperty,
        InputArguments $inputArguments,
        BaseField $field,
    ) {
        $this->name = $refProperty->name;

        $this->typeHints = PropertyHelper::getTypeHints($refProperty);

        $this->initializedStatus = PropertyHelper::getInitializedStatus($refProperty, $inputArguments, $field);

        // 入力値と初期化済みプロパティの両方が存在しない場合
        if ($this->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            $this->value = null;
            $this->valueType = PropertyValueType::NULL;
            return;
        }

        $this->value = PropertyHelper::getValue($this->initializedStatus, $refProperty, $inputArguments, $field);

        $this->valueType = PropertyHelper::getValueType($this->value);
    }


    /**
     * プロパティが未初期化状態か
     */
    public function isUninitializedProperty(): bool
    {
        return $this->initializedStatus === PropertyInitializedStatus::UNINITIALIZED;
    }

    /**
     * プロパティに値を設定
     *
     * @throws TypeError
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
