<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Support\PropertyValue;
use PhpValueObject\Support\TypeHint;
use TypeError;

/**
 * プリミティブ型の型チェックを行うバリデータ
 */
class PrimitiveTypeValidator extends CorePropertyValidator
{
    /**
     * プリミティブ型の型チェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるためバリデーション
     * ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば不要
     *
     * @throws TypeError
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // 未初期化プロパティの場合は型チェックをスキップ
        if ($this->metadata->initializedStatus === PropertyInitializedStatus::UNINITIALIZED && $value === null) {
            $validatedValue = $value;

            if ($handler !== null) {
                return $handler($validatedValue);
            }

            return $validatedValue;
        }

        $propertyValue = PropertyValue::fromValue($value);

        $isIntsersectionTypeAndObjectValue = array_any(
            $this->metadata->typeHints,
            fn(TypeHint $typeHint): bool => $typeHint->isIntersection && $propertyValue->valueType === PropertyValueType::OBJECT,
        );

        // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
        if ($isIntsersectionTypeAndObjectValue) {
            $validatedValue = $value;

            if ($handler !== null) {
                return $handler($validatedValue);
            }

            return $validatedValue;
        }

        $onlyPrimitiveTypes = array_filter(
            $this->metadata->typeHints,
            fn(TypeHint $typeHint): bool => $typeHint->isPrimitive,
        );

        // プリミティブ型が存在しない場合はPHPの型検査に任せる
        if (empty($onlyPrimitiveTypes)) {
            $validatedValue = $value;

            if ($handler !== null) {
                return $handler($validatedValue);
            }

            return $validatedValue;
        }

        $hasPrimitiveTypeAndValue = array_any(
            $onlyPrimitiveTypes,
            fn(TypeHint $typeHint): bool => $typeHint->type->value === $propertyValue->valueType->shorthand(),
        );

        // プリミティブ型が存在する場合、プロパティの型と入力値の型がひとつでも一致したらOK
        if ($hasPrimitiveTypeAndValue) {
            $validatedValue = $value;

            if ($handler !== null) {
                return $handler($validatedValue);
            }

            return $validatedValue;
        }

        $errorTypeName = join(
            '|',
            array_map(fn(TypeHint $typeHint): string => $typeHint->type->value, $onlyPrimitiveTypes),
        );

        throw new TypeError(
            "Cannot assign {$propertyValue->valueType->value} to property {$this->metadata->class}::\${$this->metadata->name} of type {$errorTypeName}",
        );
    }
}
