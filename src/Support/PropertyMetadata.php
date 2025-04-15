<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Helpers\PropertyHelper;
use ReflectionProperty;

/**
 * プロパティの静的な情報を保持するクラス
 */
final class PropertyMetadata
{
    /**
     * @param TypeHint[] $typeHints
     */
    public function __construct(
        public readonly string $class,
        public readonly string $name,
        public readonly array $typeHints,
        public readonly PropertyInitializedStatus $initializedStatus,
    ) {}

    public static function fromReflection(
        ReflectionProperty $refProperty,
        InputData $inputData,
        BaseField $field,
    ): self {
        $typeHints = PropertyHelper::getTypeHints($refProperty);
        $initializedStatus = PropertyHelper::getInitializedStatus($refProperty, $inputData, $field);

        return new self($refProperty->class, $refProperty->name, $typeHints, $initializedStatus);
    }
}
