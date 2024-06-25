<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionMethod;
use ReflectionParameter;
use TypeError;
use Akitanabe\PhpValueObject\Dto\TypeCheckDto;
use Akitanabe\PhpValueObject\Exceptions\BaseValueObjectException;
use Akitanabe\PhpValueObject\Exceptions\PhpValueObjectValidationException;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Validation\Validatable;
use ReflectionAttribute;
use ReflectionProperty;

abstract class BaseValueObject
{
    private Strict $strict;

    /**
     * @param mixed[] $args
     */
    public function __construct(...$args)
    {
        $refClass = new ReflectionClass($this);

        $this->strict = new Strict($refClass);

        $refConstructor = $refClass->getConstructor();

        // コンストラクタがオーバーライドされている場合、子クラスのコンストラクタパラメータから引数を設定する
        if ($refConstructor->getDeclaringClass()->name !== self::class) {
            $args = $this->toNamedArgs($refConstructor, $args);
        }

        foreach ($refClass->getProperties() as $property) {
            $propertyName = $property->getName();

            if (array_key_exists($propertyName, $args) === false) {
                // 初期化されている場合はバリデーションのみ ※デフォルト値含む
                if ($property->isInitialized($this)) {
                    $this->validateProperty($property);
                    continue;
                }

                // 未初期化プロパティが許可されている場合はスキップ
                if ($this->strict->uninitializedProperty->allow()) {
                    continue;
                }

                throw new BaseValueObjectException(
                    "{$refClass->name}::\${$propertyName} is not initialized. not allow uninitialized property."
                );
            }

            $value = $args[$propertyName];

            $this->checkType(
                $property->getType(),
                $propertyName,
                $value,
            );

            $property->setValue(
                $this,
                $value,
            );

            // プロパティ値バリデーション
            $this->validateProperty($property);
        }
    }

    /**
     * 
     * 子クラスのコンストラクタから引数情報を取得して、
     * 渡された引数を名前付き引数で渡されたように変換する
     * 
     * @param ReflectionMethod $refConstructor
     * @param mixed[] $args
     * 
     * @return mixed[]
     * 
     */
    private function toNamedArgs(ReflectionMethod $refConstructor, array $args): array
    {
        $overrideArgs = array_reduce(
            $refConstructor->getParameters(),
            function (array $newArgs, ReflectionParameter $param) use ($args) {
                $paramName = $param->getName();
                $paramPosition = $param->getPosition();

                // 渡された引数が名前付き引数か不明なので、引数の名前と位置で取得
                if (array_key_exists($paramPosition, $args)) {
                    $newArgs[$paramName] = $args[$paramPosition];
                } elseif (array_key_exists($paramName, $args)) {
                    $newArgs[$paramName] = $args[$paramName];
                    // デフォルト値が存在した場合は取得
                } elseif ($param->isDefaultValueAvailable()) {
                    $newArgs[$paramName] = $param->getDefaultValue();
                }

                return $newArgs;
            },
            [],
        );

        // 渡された引数のうち、子クラスのコンストラクタに定義されていない引数を取得
        // 名前付き引数しか対応しない
        foreach ($args as $key => $value) {
            if (
                is_int($key) === false
                && array_key_exists($key, $overrideArgs) === false
            ) {
                $overrideArgs[$key] = $value;
            }
        }

        return $overrideArgs;
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
    private function checkType(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType,
        string $propertyName,
        mixed $value
    ): void {

        $className = static::class;
        $valueType = $this->getValueType($value);

        $checkTypes = $this->extractPropertyTypeToTypeCheckDtos($propertyType, $value);

        foreach ($checkTypes as $typeCheckDto) {

            if (
                // 型が指定されていない場合
                ($typeCheckDto->typeName === "none" && $this->strict->noneTypeProperty->allow() === false)
                // mixed型の場合
                || ($typeCheckDto->typeName === 'mixed' && $this->strict->mixedTypeProperty->allow() === false)
            ) {
                throw new TypeError(
                    "{$className}::\${$propertyName} is not type defined. ValueObject does not allowed {$typeCheckDto->typeName} type."
                );
            }

            // プロパティ型がIntersectionTypeで入力値がobjectの時はPHPの型検査に任せる
            if ($typeCheckDto->isIntersection && $typeCheckDto->valueType === 'object') {
                return;
            }
        }

        // プリミティブ型のみ型をチェックする
        // ReflectionProperty::setValueでプリミティブ型もチェックされるようになれば以下の処理は不要
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

    /**
     * プロパティの型情報をTypeCheckDtoに変換して抽出
     * 
     * @param ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType
     * @param mixed $inputValue
     * 
     * @return TypeCheckDto[]
     */
    private function extractPropertyTypeToTypeCheckDtos(
        ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $propertyType,
        mixed $inputValue,

    ): array {
        $types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];

        return array_map(
            fn (ReflectionNamedType|ReflectionIntersectionType|null $type): TypeCheckDto => $this->getTypeCheckDto($type, $inputValue),
            $types,
        );
    }

    /**
     * gettypeの結果をPHPの型名に変換
     * 
     * @param mixed $value
     * 
     * @return string
     * 
     */
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

    /**
     * 
     * プロパティの型と入力値の型をTypeCheckDtoに変換
     * 
     * @param ReflectionNamedType|ReflectionIntersectionType|null $propertyType
     * @param mixed $value
     * 
     * @return TypeCheckDto
     * 
     */
    private function getTypeCheckDto(
        ReflectionNamedType|ReflectionIntersectionType|null $propertyType,
        mixed $value,
    ): TypeCheckDto {
        $valueType = $this->getValueType($value);

        if ($propertyType === null) {
            return new TypeCheckDto('none', $valueType);
        }

        if ($propertyType instanceof ReflectionIntersectionType) {
            return new TypeCheckDto('object', $valueType, isIntersection: true);
        }

        $typeName = $propertyType->getName();

        if ($typeName === "mixed") {
            return new TypeCheckDto('mixed', $valueType);
        }

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
     * プロパティに設定されているAttributeからバリデーションを実行
     * 
     * @param ReflectionProperty $refProp
     * @return void
     * 
     * @throws PhpValueObjectValidationException
     */
    private function validateProperty(ReflectionProperty $refProp): void
    {
        $attributes = $refProp->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);
        $value = $refProp->getValue($this);

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance->validate($value, $refProp) === false) {
                throw new PhpValueObjectValidationException(
                    $attributeInstance,
                    $refProp,
                );
            }
        }
    }
}
