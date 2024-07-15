<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Helpers;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionIntersectionType;
use TypeError;
use Akitanabe\PhpValueObject\Dto\PropertyDto;
use Akitanabe\PhpValueObject\Dto\TypeHintsDto;
use Akitanabe\PhpValueObject\Enums\TypeHintsDtoType;
use Akitanabe\PhpValueObject\Options\Strict;

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
     * @template T of object
     * @param ReflectionClass<T> $refClass
     * @param Strict $strict
     * @param PropertyDto $propertyDto
     * 
     * @return void
     * 
     * @throws TypeError
     * 
     */
    static public function checkType(
        ReflectionClass $refClass,
        Strict $strict,
        PropertyDto $propertyDto,
    ): void {
        $typeHints = array_map(
            fn (ReflectionNamedType|ReflectionIntersectionType|null $type): TypeHintsDto => new TypeHintsDto($type),
            $propertyDto->types,
        );

        foreach ($typeHints as $typeHintsDto) {

            if (
                // 型が指定されていない場合
                ($typeHintsDto->type === TypeHintsDtoType::NONE && $strict->noneTypeProperty->disallow())
                // mixed型の場合
                || ($typeHintsDto->type === TypeHintsDtoType::MIXED && $strict->mixedTypeProperty->disallow())
            ) {
                throw new TypeError(
                    "{$refClass->name}::\${$propertyDto->name} is not type defined. ValueObject does not allowed {$typeHintsDto->type->value} type."
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
            if ($typeHintsDto->type->value === $propertyDto->valueType) {
                return;
            }
        }

        $errorTypeName = join(
            '|',
            array_map(
                fn (TypeHintsDto $typeHintsDto): string => $typeHintsDto->type->value,
                $onlyPrimitiveTypes,
            ),
        );

        throw new TypeError(
            "Cannot assign {$propertyDto->valueType} to property {$refClass->name}::\${$propertyDto->name} of type {$errorTypeName}"
        );
    }
}
