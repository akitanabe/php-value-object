<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Helpers\AssertionHelper;
use PhpValueObject\Helpers\PropertyHelper;
use ReflectionProperty;
use PhpValueObject\Exceptions\ValidationException;
use TypeError;

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
    ) {
    }

    public static function create(
        ReflectionProperty $refProperty,
        InputData $inputData,
        BaseField $field,
    ): self {
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
     * @throws ValidationException
     * @throws TypeError
     */
    public function getPropertyValue(BaseField $field, FieldValidationManager $validationManager): mixed
    {
        // バリデーションの実行
        $value = $validationManager->processValidation($this->value);

        // 入力前にプリミティブ型のチェック
        AssertionHelper::assertPrimitiveType($this->typeHints, $value, $this->class, $this->name);

        return $value;
    }
}
