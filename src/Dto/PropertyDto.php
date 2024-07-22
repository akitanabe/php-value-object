<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Dto;

use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Helpers\TypeHelper;
use Akitanabe\PhpValueObject\Support\InputArguments;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

final class PropertyDto
{
    private const string UNINITIALIZED_VALUE_TYPE = "uninitialized";

    public readonly string $name;
    public readonly bool $isInitialized;
    public readonly bool $isInputValue;

    /**  @var (ReflectionNamedType|ReflectionIntersectionType|null)[]  */
    public readonly array $types;
    public readonly mixed $value;
    public readonly string $valueType;

    /**
     * @param BaseValueObject $vo
     * @param ReflectionProperty $refProperty
     * @param InputArguments $inputArguments
     * 
     */
    public function __construct(
        BaseValueObject $vo,
        ReflectionProperty $refProperty,
        InputArguments $inputArguments,
    ) {
        $this->name = $refProperty->name;
        $this->isInitialized = $refProperty->isInitialized($vo);
        $this->isInputValue = $inputArguments->hasValue($refProperty->name);

        $propertyType = $refProperty->getType();

        // PHPStanのチェックで継承元のReflectionType|nullが入ってくるので無視する(設定にある？)
        // @phpstan-ignore assign.propertyType
        $this->types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];


        // 入力値と初期化済みプロパティの両方が存在しない場合
        if ($this->isInputValue === false && $this->isInitialized === false) {
            $this->value = null;
            $this->valueType = self::UNINITIALIZED_VALUE_TYPE;
            return;
        }

        $this->value = ($this->isInputValue)
            ? $inputArguments->getValue($refProperty->name)
            : $refProperty->getValue($vo);

        $this->valueType = TypeHelper::getValueType($this->value);
    }

    /**
     * プロパティが未初期化状態か判定する
     * 
     * @return bool
     */
    public function isUninitialized(): bool
    {
        return $this->valueType === self::UNINITIALIZED_VALUE_TYPE;
    }
}
