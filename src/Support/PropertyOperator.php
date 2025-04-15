<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Helpers\PropertyHelper;
use ReflectionProperty;

final class PropertyOperator
{
    public function __construct(
        public readonly PropertyMetadata $metadata,
        public readonly PropertyValue $value,
    ) {}

    public static function create(ReflectionProperty $refProperty, InputData $inputData, BaseField $field): self
    {
        $metadata = PropertyMetadata::fromReflection($refProperty, $inputData, $field);

        $value = match ($metadata->initializedStatus) {
            PropertyInitializedStatus::UNINITIALIZED => null,
            default => PropertyHelper::getValue($metadata->initializedStatus, $refProperty, $inputData, $field),
        };

        return new self($metadata, PropertyValue::fromValue($value));
    }

    /**
     * 新しい値で新しいPropertyOperatorを作成する
     */
    public function withValue(mixed $value): self
    {
        return new self($this->metadata, PropertyValue::fromValue($value));
    }
}
