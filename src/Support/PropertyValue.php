<?php

declare(strict_types=1);

namespace PhSculptis\Support;

use PhSculptis\Enums\PropertyValueType;
use PhSculptis\Helpers\PropertyHelper;

/**
 * プロパティの値と型情報を保持するクラス
 */
final class PropertyValue
{
    public function __construct(
        public readonly mixed $value,
        public readonly PropertyValueType $valueType,
    ) {}

    public static function fromValue(mixed $value): self
    {
        $valueType = PropertyHelper::getValueType($value);

        return new self($value, $valueType);
    }
}
