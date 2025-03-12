<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Helpers\PropertyHelper;
use ReflectionProperty;

final class PropertyOperator
{
    public readonly string $class;

    public readonly string $name;

    public readonly PropertyInitializedStatus $initializedStatus;

    public readonly mixed $value;

    public readonly PropertyValueType $valueType;

    /** @var TypeHint[] */
    public readonly array $typeHints;

    public function __construct(
        ReflectionProperty $refProperty,
        InputData $inputData,
        BaseField $field,
    ) {
        $this->class = $refProperty->class;
        $this->name = $refProperty->name;

        $this->typeHints = PropertyHelper::getTypeHints($refProperty);

        $this->initializedStatus = PropertyHelper::getInitializedStatus($refProperty, $inputData, $field);

        // 入力値と初期化済みプロパティの両方が存在しない場合
        if ($this->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            $this->value = null;
            $this->valueType = PropertyValueType::NULL;
            return;
        }

        $this->value = PropertyHelper::getValue($this->initializedStatus, $refProperty, $inputData, $field);

        $this->valueType = PropertyHelper::getValueType($this->value);
    }
}