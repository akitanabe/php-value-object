<?php

declare(strict_types=1);

namespace PhpValueObject\Helpers;

use PhpValueObject\Support\TypeHints;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\PropertyValueType;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Support\InputArguments;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
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
     * プロパティの型ヒントを取得
     *
     * @return TypeHints[]
     */
    public static function getTypeHints(ReflectionProperty $refProperty): array
    {
        $propertyType = $refProperty->getType();

        /**
         * @var (ReflectionNamedType|ReflectionIntersectionType|null)[] $types
         */
        $types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];


        return array_map(
            fn(ReflectionNamedType|ReflectionIntersectionType|null $type): TypeHints => new TypeHints($type),
            $types,
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
