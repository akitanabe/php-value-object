<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use TypeError;

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

    /**
     * 型のチェック
     * RelectionProperty::setValueにプリミティブ型を渡すとTypeErrorにならずにキャストされるため
     * プリミティブ型のみ型をチェックする
     * 
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType
     * @@param string $propertyName
     * @param mixed $value
     * 
     * @throws TypeError
     */
    private function typeCheck(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType,
        string $propertyName,
        mixed $value
    ): void {

        $className = static::class;

        // 共用型や交差型などの場合は再帰的にチェック
        if (
            $propertyType !== null &&
            $propertyType instanceof ReflectionNamedType === false
        ) {
            foreach ($propertyType->getTypes() as $type) {
                $this->typeCheck($type, $propertyName, $value);
            }

            return;
        }

        $typeName = $propertyType?->getName();

        // 型が指定されていない場合、もしくはmixedな場合はエラー
        if ($typeName === null || $typeName === 'mixed') {
            throw new TypeError(
                "{$className}::\${$propertyName} is not type defined. ValueObject does not allowed unknown type."
            );
        }


        // プリミティブ型のチェックのみでその他の型はPHPの型チェックに任せる
        if (match ($typeName) {
            'int' => is_int($value),
            'string' => is_string($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            default => true,
        }) {
            return;
        };

        $valueType = gettype($value);

        throw new TypeError(
            "Cannot assign {$valueType} to property {$className}::\${$propertyName} of type {$typeName}"
        );
    }
}
