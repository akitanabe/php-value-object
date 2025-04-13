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
    /**
     * @param TypeHint[] $typeHints
     */
    private function __construct(
        public readonly string $class,
        public readonly string $name,
        public readonly array $typeHints,
        public readonly PropertyInitializedStatus $initializedStatus,
        public readonly mixed $value,
        public readonly PropertyValueType $valueType,
    ) {}

    public static function create(ReflectionProperty $refProperty, InputData $inputData, BaseField $field): self
    {
        $typeHints = PropertyHelper::getTypeHints($refProperty);
        $initializedStatus = PropertyHelper::getInitializedStatus($refProperty, $inputData, $field);

        $value = null;
        $valueType = PropertyValueType::NULL;

        if ($initializedStatus !== PropertyInitializedStatus::UNINITIALIZED) {
            $value = PropertyHelper::getValue($initializedStatus, $refProperty, $inputData, $field);
            $valueType = PropertyHelper::getValueType($value);
        }

        return new self(
            $refProperty->class,
            $refProperty->name,
            $typeHints,
            $initializedStatus,
            $value,
            $valueType,
        );
    }

    /**
     * @param BaseField $field
     * @param FieldValidationManager $validationManager
     * @return mixed
     */
    /**
     * 新しい値で新しいPropertyOperatorを作成する
     */
    public function withValue(mixed $value): self
    {
        // 値の型を判定
        $valueType = PropertyHelper::getValueType($value);

        return new self(
            $this->class,
            $this->name,
            $this->typeHints,
            $this->initializedStatus,
            $value,
            $valueType,
        );
    }

}
