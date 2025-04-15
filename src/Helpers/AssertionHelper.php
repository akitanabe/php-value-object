<?php

namespace PhpValueObject\Helpers;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Support\PropertyOperator;
use ReflectionClass;
use PhpValueObject\BaseModel;
use TypeError;

class AssertionHelper
{
    /**
     * @param ReflectionClass<BaseModel> $refClass
     *
     * @throws InheritableClassException
     */
    public static function assertInheritableClass(ReflectionClass $refClass, ModelConfig $modelConfig): void
    {
        if (
            $refClass->isFinal() === false
            && $modelConfig->inheritableClass->disallow()
        ) {

            throw new InheritableClassException(
                "{$refClass->name} is not allowed to inherit. not allow inheritable class.",
            );
        }
    }

    /**
     * プロパティの状態を検証
     *
     * @throws InvalidPropertyStateException
     */
    public static function assertInvalidPropertyStateOrSkip(
        ModelConfig $modelConfig,
        FieldConfig $fieldConfig,
        PropertyOperator $propertyOperator,
    ): bool {

        // プロパティが未初期化の場合
        if ($propertyOperator->metadata->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            // 未初期化プロパティが許可されている場合はスキップ
            if ($modelConfig->uninitializedProperty->allow() || $fieldConfig->uninitializedProperty->allow()) {
                return true;
            }

            throw new InvalidPropertyStateException(
                "{$propertyOperator->metadata->class}::\${$propertyOperator->metadata->name} is not initialized. not allow uninitialized property.",
            );
        }

        foreach ($propertyOperator->metadata->typeHints as $typeHint) {
            if (
                (
                    // 型が指定されていない場合
                    $typeHint->type === TypeHintType::NONE
                    && ($modelConfig->noneTypeProperty->disallow() && $fieldConfig->noneTypeProperty->disallow())
                )
                || (
                    // mixed型の場合
                    $typeHint->type === TypeHintType::MIXED
                    && ($modelConfig->mixedTypeProperty->disallow() && $fieldConfig->mixedTypeProperty->disallow())
                )
            ) {
                throw new InvalidPropertyStateException(
                    "{$propertyOperator->metadata->class}::\${$propertyOperator->metadata->name} is invalid property state. not allow {$typeHint->type->value} property type.",
                );
            }
        }

        return false;
    }

    /**
     * プリミティブ型の型チェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるためアサーション
     * ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば不要
     *
     * @throws TypeError
     */
    public static function assertPrimitiveType(PropertyOperator $propertyOperator): void
    {
        $isIntsersectionTypeAndObjectValue = array_any(
            $propertyOperator->metadata->typeHints,
            fn(TypeHint $typeHint): bool => $typeHint->isIntersection && $propertyOperator->value->valueType === PropertyValueType::OBJECT,
        );

        // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
        if ($isIntsersectionTypeAndObjectValue) {
            return;
        }

        $onlyPrimitiveTypes = array_filter(
            $propertyOperator->metadata->typeHints,
            fn(TypeHint $typeHint): bool => $typeHint->isPrimitive,
        );

        // プリミティブ型が存在しない場合はPHPの型検査に任せる
        if (empty($onlyPrimitiveTypes)) {
            return;
        }

        $hasPrimitiveTypeAndValue = array_any(
            $onlyPrimitiveTypes,
            fn(TypeHint $typeHint): bool => $typeHint->type->value === $propertyOperator->value->valueType->shorthand(),
        );

        // プリミティブ型が存在する場合、プロパティの型と入力値の型がひとつでも一致したらOK
        if ($hasPrimitiveTypeAndValue) {
            return;
        }

        $errorTypeName = join(
            '|',
            array_map(fn(TypeHint $typeHint): string => $typeHint->type->value, $onlyPrimitiveTypes),
        );

        throw new TypeError(
            "Cannot assign {$propertyOperator->value->valueType->value} to property {$propertyOperator->metadata->class}::\${$propertyOperator->metadata->name} of type {$errorTypeName}",
        );
    }
}
