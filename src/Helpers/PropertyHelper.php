<?php

declare(strict_types=1);

namespace PhpValueObject\Helpers;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Dto\TypeHintsDto;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Enums\TypeHintsDtoType;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Support\InputArguments;
use PhpValueObject\Support\PropertyOperator;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use TypeError;
use PhpValueObject\BaseModel;
use UnexpectedValueException;

final class PropertyHelper
{
    /**
     * プロパティの初期化状態に応じて値を取得
     */
    public static function getValue(
        PropertyInitializedStatus $initializedStatus,
        ReflectionProperty $refProperty,
        InputArguments $inputArguments,
        BaseField $field,
    ): mixed {
        return match (true) {
            $initializedStatus === PropertyInitializedStatus::BY_FACTORY => $field->defaultFactory(
                $inputArguments->inputs,
            ),
            $initializedStatus === PropertyInitializedStatus::BY_INPUT => $inputArguments->getValue(
                $refProperty->name,
                $field->alias,
            ),
            default => $refProperty->getDefaultValue(),
        };
    }

    /**
     * gettypeの結果をPHPの型名に変換
     */
    public static function getValueType(mixed $value): PropertyValueType
    {
        $typeName = gettype(value: $value);

        return PropertyValueType::from(value: $typeName);
    }

    /**
     * 型のチェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるため
     * プリミティブ型のみ型をチェックする
     *
     * @param ReflectionClass<BaseModel> $refClass
     *
     * @throws TypeError
     */
    public static function checkType(
        ReflectionClass $refClass,
        ModelConfig $modelConfig,
        FieldConfig $fieldConfig,
        PropertyOperator $propertyOperator,
    ): void {
        $typeHints = array_map(
            fn(ReflectionNamedType|ReflectionIntersectionType|null $type): TypeHintsDto => new TypeHintsDto($type),
            $propertyOperator->types,
        );

        foreach ($typeHints as $typeHintsDto) {

            if (
                // 型が指定されていない場合
                (
                    $typeHintsDto->type === TypeHintsDtoType::NONE
                    && ($modelConfig->noneTypeProperty->disallow() && $fieldConfig->noneTypeProperty->disallow())
                )
                // mixed型の場合
                || (
                    $typeHintsDto->type === TypeHintsDtoType::MIXED
                    && ($modelConfig->mixedTypeProperty->disallow() && $fieldConfig->mixedTypeProperty->disallow())
                )
            ) {
                throw new TypeError(
                    "{$refClass->name}::\${$propertyOperator->name} is not type defined. ValueObject does not allowed {$typeHintsDto->type->value} type.",
                );
            }

            // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
            if ($typeHintsDto->isIntersection && $propertyOperator->valueType === PropertyValueType::OBJECT) {
                return;
            }
        }

        // プリミティブ型のみ型をチェックする
        // ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば以下の処理は不要
        $onlyPrimitiveTypes = array_filter(
            $typeHints,
            fn(TypeHintsDto $typeHintsDto): bool => $typeHintsDto->isPrimitive,
        );

        // プリミティブ型が存在しない場合はPHPの型検査に任せる
        if (count($onlyPrimitiveTypes) === 0) {
            return;
        }

        // プリミティブ型が存在する場合、プロパティの型と入力値の型がひとつでも一致したらOK
        foreach ($onlyPrimitiveTypes as $typeHintsDto) {
            if ($typeHintsDto->type->value === $propertyOperator->valueType->shorthand()) {
                return;
            }
        }

        $errorTypeName = join(
            '|',
            array_map(fn(TypeHintsDto $typeHintsDto): string => $typeHintsDto->type->value, $onlyPrimitiveTypes),
        );

        throw new TypeError(
            "Cannot assign {$propertyOperator->valueType->value} to property {$refClass->name}::\${$propertyOperator->name} of type {$errorTypeName}",
        );
    }

    /**
     * プロパティの初期化状態を取得
     *
     * @throws UnexpectedValueException
     */
    public static function getInitializedStatus(
        ReflectionProperty $refProperty,
        InputArguments $inputArguments,
        BaseField $field,
    ): PropertyInitializedStatus {
        // プロパティの初期化状態を判定
        $hasInputValue = $inputArguments->hasValue($refProperty->name, $field->alias);
        $hasDefaultFactory = $field->hasDefaultFactory();
        $hasDefaultValue = $refProperty->hasDefaultValue();

        // デフォルトファクトリーとデフォルト値が両方存在する場合は例外を投げる
        if ($hasDefaultFactory && $hasDefaultValue) {
            throw new UnexpectedValueException("{$refProperty->name} has both default factory and default value.");
        }

        return match (true) {
            // デフォルトファクトリが存在する場合
            $hasDefaultFactory => PropertyInitializedStatus::BY_FACTORY,

            // 外部入力が存在
            $hasInputValue => PropertyInitializedStatus::BY_INPUT,

            // デフォルト値が存在
            $hasDefaultValue => PropertyInitializedStatus::BY_DEFAULT,

            // 未初期化
            default => PropertyInitializedStatus::UNINITIALIZED,
        };
    }

}
