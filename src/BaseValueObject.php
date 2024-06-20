<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use TypeError;
use Akitanabe\PhpValueObject\Dto\TypeCheckDto;

abstract class BaseValueObject
{
    /**
     * @param mixed[] $args
     */
    public function __construct(...$args)
    {
        $refClass = new ReflectionClass($this);

        foreach ($refClass->getProperties() as $property) {
            $propertyName = $property->getName();

            if (array_key_exists($propertyName, $args) === false) {
                continue;
            }

            $value = $args[$propertyName];


            $this->typeCheck(
                $property->getType(),
                $propertyName,
                $value,
            );

            $property->setValue(
                $this,
                $value,
            );
        }
    }

    private function getValueType(mixed $value): string
    {
        $typeName = gettype($value);

        return match ($typeName) {
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => strtolower($typeName),
        };
    }

    private function getTypeCheckDto(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
        mixed $value,
    ): TypeCheckDto {
        $valueType = $this->getValueType($value);

        if ($propertyType === null) {
            return new TypeCheckDto('null', $valueType);
        }

        if ($propertyType === "mixed") {
            return new TypeCheckDto('mixed', $valueType);
        }

        if ($propertyType instanceof ReflectionIntersectionType) {
            return new TypeCheckDto('object', $valueType, isIntersection: true);
        }

        $typeName = $propertyType->getName();


        if (in_array($typeName, [
            'int',
            'string',
            'float',
            'bool',
        ], true)) {
            return new TypeCheckDto($typeName, $valueType, isPrimivtive: true);
        }

        return new TypeCheckDto("object", $valueType);
    }

    /**
     * 
     * 型のチェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるため
     * プリミティブ型のみ型をチェックする
     * 
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType
     * @param string $propertyName
     * @param mixed $value
     * 
     * @throws TypeError
     * 
     */
    private function typeCheck(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType,
        string $propertyName,
        mixed $value
    ): void {

        $className = static::class;
        $valueType = $this->getValueType($value);

        $types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];

        /** @var TypeCheckDto[] $checkTypes */
        $checkTypes = [];
        foreach ($types as $type) {
            $typeCheckDto = $this->getTypeCheckDto($type, $value);

            // 型が指定されていない場合、もしくはmixedな場合はエラー
            if ($typeCheckDto->typeName === "null" || $typeCheckDto->typeName === 'mixed') {
                throw new TypeError(
                    "{$className}::\${$propertyName} is not type defined. ValueObject does not allowed unknown type."
                );
            }

            // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
            if ($typeCheckDto->isIntersection && $typeCheckDto->valueType === 'object') {
                return;
            }

            $checkTypes[] = $typeCheckDto;
        }


        // プリミティブ型のみ型をチェックする
        $onlyPrimitiveTypes = array_filter(
            $checkTypes,
            fn (TypeCheckDto $typeCheckDto): bool => $typeCheckDto->isPrimivtive,
        );

        // プリミティブ型が存在しない場合はPHPの型検査に任せる
        if (count($onlyPrimitiveTypes) === 0) {
            return;
        }

        // プリミティブ型が存在する場合、プロパティの型と入力値の型がひとつでも一致したらOK
        foreach ($onlyPrimitiveTypes as $typeCheckDto) {
            if ($typeCheckDto->typeName === $typeCheckDto->valueType) {
                return;
            }
        }

        $errorTypeName = join(
            '|',
            array_map(
                fn (TypeCheckDto $typeCheckDto): string => $typeCheckDto->typeName,
                $onlyPrimitiveTypes,
            ),
        );

        throw new TypeError(
            "Cannot assign {$valueType} to property {$className}::\${$propertyName} of type {$errorTypeName}"
        );
    }
}