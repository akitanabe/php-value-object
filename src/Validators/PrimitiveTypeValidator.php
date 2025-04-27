<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Support\PropertyValue;
use PhpValueObject\Support\TypeHint;
use TypeError;

/**
 * プリミティブ型の型チェックを行うバリデータ
 */
class PrimitiveTypeValidator implements Validatorable
{
    private readonly PropertyMetadata $metadata;

    public function __construct(PropertyMetadata $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * プリミティブ型の型チェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるためバリデーション
     * ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば不要
     *
     * @throws TypeError
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // $handlerがnullかどうかで処理を分岐するアロー関数
        $processValue = fn(mixed $v) => $handler !== null ? $handler($v) : $v;

        // 未初期化プロパティの場合は型チェックをスキップ
        if ($this->metadata->initializedStatus === PropertyInitializedStatus::UNINITIALIZED && $value === null) {
            return $processValue($value);
        }

        $propertyValue = PropertyValue::fromValue($value);

        $isIntsersectionTypeAndObjectValue = array_any(
            $this->metadata->typeHints,
            fn(TypeHint $typeHint): bool => $typeHint->isIntersection && $propertyValue->valueType === PropertyValueType::OBJECT,
        );

        // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
        if ($isIntsersectionTypeAndObjectValue) {
            return $processValue($value);
        }

        $onlyPrimitiveTypes = array_filter(
            $this->metadata->typeHints,
            fn(TypeHint $typeHint): bool => $typeHint->isPrimitive,
        );

        // プリミティブ型が存在しない場合はPHPの型検査に任せる
        if (empty($onlyPrimitiveTypes)) {
            return $processValue($value);
        }

        $hasPrimitiveTypeAndValue = array_any(
            $onlyPrimitiveTypes,
            fn(TypeHint $typeHint): bool => $typeHint->type->value === $propertyValue->valueType->shorthand(),
        );

        // プリミティブ型が存在する場合、プロパティの型と入力値の型がひとつでも一致したらOK
        if ($hasPrimitiveTypeAndValue) {
            return $processValue($value);
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
