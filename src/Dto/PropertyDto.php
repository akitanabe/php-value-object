<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Dto;

use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Helpers\TypeHelper;
use ReflectionProperty;
use ReflectionUnionType;

final class PropertyDto
{
    private const string UNINITIALIZED_VALUE_TYPE = "uninitialized";

    public string $name;
    public bool $isInitialized;
    public bool $isInputValue;
    public array $types;
    public mixed $value = null;
    public string $valueType = self::UNINITIALIZED_VALUE_TYPE;

    public function __construct(
        BaseValueObject $vo,
        ReflectionProperty $refProperty,
        array $inputArgs
    ) {
        $this->name = $refProperty->name;
        $this->isInitialized = $refProperty->isInitialized($vo);
        $this->isInputValue = array_key_exists($refProperty->name, $inputArgs);

        $propertyType = $refProperty->getType();
        $this->types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];


        // 入力値と初期化済みプロパティの両方が存在しない場合
        if ($this->isInputValue === false && $this->isInitialized === false) {
            return;
        }

        $this->value = ($this->isInputValue)
            ? $inputArgs[$refProperty->name]
            : $refProperty->getValue($vo);

        $this->valueType = TypeHelper::getValueType($this->value);
    }

    /**
     * プロパティが未初期かどうか判定する
     * 
     * @return bool
     */
    public function isUninitialized(): bool
    {
        return $this->valueType === self::UNINITIALIZED_VALUE_TYPE;
    }
}