<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Helpers;

use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionIntersectionType;
use TypeError;
use Akitanabe\PhpValueObject\Dto\TypeHintsDto;
use Akitanabe\PhpValueObject\Options\Strict;
use ReflectionClass;

final class TypeHelper
{
    /**
     * gettypeの結果をPHPの型名に変換
     * 
     * @param mixed $value
     * 
     * @return string
     * 
     */
    static public function getValueType(mixed $value): string
    {
        $typeName = gettype($value);

        return match ($typeName) {
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => strtolower($typeName),
        };
    }

    /**
     * 
     * 型のチェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるため
     * プリミティブ型のみ型をチェックする
     * 
     * @param ReflectionClass $refClass
     * @param Strict $strict
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType
     * @param string $propertyName
     * @param mixed $value
     * 
     * @throws TypeError
     * 
     */
    static public function checkType(
        ReflectionClass $refClass,
        Strict $strict,
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType,
        string $propertyName,
        mixed $value
    ): void {

        $valueType = self::getValueType($value);

        $typeHints = self::toTypeHintsDtos($propertyType, $value);

        foreach ($typeHints as $typeHintsDto) {

            if (
                // 型が指定されていない場合
                ($typeHintsDto->typeName === "none" && $strict->noneTypeProperty->disallow())
                // mixed型の場合
                || ($typeHintsDto->typeName === 'mixed' && $strict->mixedTypeProperty->disallow())
            ) {
                throw new TypeError(
                    "{$refClass->name}::\${$propertyName} is not type defined. ValueObject does not allowed {$typeHintsDto->typeName} type."
                );
            }

            // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
            if ($typeHintsDto->isIntersection && $typeHintsDto->valueType === 'object') {
                return;
            }
        }

        // プリミティブ型のみ型をチェックする
        // ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば以下の処理は不要
        $onlyPrimitiveTypes = array_filter(
            $typeHints,
            fn (TypeHintsDto $typeHintsDto): bool => $typeHintsDto->isPrimitive,
        );

        // プリミティブ型が存在しない場合はPHPの型検査に任せる
        if (count($onlyPrimitiveTypes) === 0) {
            return;
        }

        // プリミティブ型が存在する場合、プロパティの型と入力値の型がひとつでも一致したらOK
        foreach ($onlyPrimitiveTypes as $typeHintsDto) {
            if ($typeHintsDto->typeName === $typeHintsDto->valueType) {
                return;
            }
        }

        $errorTypeName = join(
            '|',
            array_map(
                fn (TypeHintsDto $typeHintsDto): string => $typeHintsDto->typeName,
                $onlyPrimitiveTypes,
            ),
        );

        throw new TypeError(
            "Cannot assign {$valueType} to property {$refClass->name}::\${$propertyName} of type {$errorTypeName}"
        );
    }

    /**
     * プロパティの型情報をTypeHintsDtoに変換して抽出
     * 
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType
     * @param mixed $inputValue
     * 
     * @return TypeHintsDto[]
     */
    static private function toTypeHintsDtos(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType,
        mixed $inputValue,

    ): array {
        $types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];

        return array_map(
            fn (ReflectionNamedType|ReflectionIntersectionType|null $type): TypeHintsDto => new TypeHintsDto($type, $inputValue),
            $types,
        );
    }
}
