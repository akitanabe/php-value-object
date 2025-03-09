<?php

namespace PhpValueObject\Helpers;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Exceptions\InheritableClassException;
use PhpValueObject\Exceptions\UninitializedException;
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
     *
     * @param ReflectionClass<BaseModel> $refClass
     *
     * @throws UninitializedException
     */
    public static function assertUninitializedPropertyOrSkip(
        ReflectionClass $refClass,
        ModelConfig $modelConfig,
        FieldConfig $fieldConfig,
        PropertyOperator $propertyOperator,
    ): bool {

        // プロパティが未初期化の場合
        if ($propertyOperator->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            // 未初期化プロパティが許可されている場合はスキップ
            if ($modelConfig->uninitializedProperty->allow() || $fieldConfig->uninitializedProperty->allow()) {
                return true;
            }

            throw new UninitializedException(
                "{$refClass->name}::\${$propertyOperator->name} is not initialized. not allow uninitialized property.",
            );
        }

        return false;
    }

    /**
     * プロパティの型をチェック
     *
     * @param ReflectionClass<BaseModel> $refClass
     *
     * @throws TypeError
     */
    public static function assertDisallowPropertyType(
        ReflectionClass $refClass,
        ModelConfig $modelConfig,
        FieldConfig $fieldConfig,
        PropertyOperator $propertyOperator,
    ): void {
        foreach ($propertyOperator->typeHints as $typeHint) {
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
                throw new TypeError(
                    "{$refClass->name}::\${$propertyOperator->name} is not type defined. ValueObject does not allowed {$typeHint->type->value} type.",
                );
            }
        }
    }

    /**
     * プリミティブ型の型チェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるためアサーション
     * ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば不要
     *
     * @param ReflectionClass<BaseModel> $refClass
     * @param PropertyOperator $propertyOperator
     * @throws TypeError
     * @return void
     */
    public static function assertPrimitiveType(
        ReflectionClass $refClass,
        PropertyOperator $propertyOperator,
    ): void {
        $typeHints = $propertyOperator->typeHints;

        foreach ($typeHints as $typeHint) {
            // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
            if ($typeHint->isIntersection && $propertyOperator->valueType === PropertyValueType::OBJECT) {
                return;
            }
        }

        // ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば以下の処理は不要
        $onlyPrimitiveTypes = array_filter(
            $typeHints,
            fn(TypeHint $typeHint): bool => $typeHint->isPrimitive,
        );

        // プリミティブ型が存在しない場合はPHPの型検査に任せる
        if (count($onlyPrimitiveTypes) === 0) {
            return;
        }

        // プリミティブ型が存在する場合、プロパティの型と入力値の型がひとつでも一致したらOK
        foreach ($onlyPrimitiveTypes as $typeHint) {
            if ($typeHint->type->value === $propertyOperator->valueType->shorthand()) {
                return;
            }
        }

        $errorTypeName = join(
            '|',
            array_map(fn(TypeHint $typeHint): string => $typeHint->type->value, $onlyPrimitiveTypes),
        );

        throw new TypeError(
            "Cannot assign {$propertyOperator->valueType->value} to property {$refClass->name}::\${$propertyOperator->name} of type {$errorTypeName}",
        );

    }

}
