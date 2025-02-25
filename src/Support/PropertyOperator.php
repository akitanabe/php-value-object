<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Support;

use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Enums\PropertyInitializedStatus;
use Akitanabe\PhpValueObject\Enums\PropertyValueType;
use Akitanabe\PhpValueObject\Helpers\TypeHelper;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

final class PropertyOperator
{
    public readonly string $name;

    public readonly PropertyInitializedStatus $initializedStatus;

    /**
     * @var (ReflectionNamedType|ReflectionIntersectionType|null)[]
     */
    public readonly array $types;

    public readonly mixed $value;

    public readonly PropertyValueType $valueType;

    public function __construct(
        protected BaseValueObject $vo,
        protected ReflectionProperty $refProperty,
        InputArguments $inputArguments,
    ) {
        $this->name = $refProperty->name;

        // 初期化状態
        $this->initializedStatus = match (true) {
            // 外部入力が存在
            $inputArguments->hasValue($refProperty->name) => PropertyInitializedStatus::INPUTED,
            // デフォルト値により初期化済み
            $refProperty->isInitialized($vo) => PropertyInitializedStatus::BY_DEFAULT,
            // 未初期化
            default => PropertyInitializedStatus::UNINITIALIZED,
        };

        // 入力値と初期化済みプロパティの両方が存在しない場合
        if ($this->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            $this->value = null;
            $this->valueType = PropertyValueType::NULL;
            return;
        }

        $propertyType = $refProperty->getType();

        // PHPStanのチェックで継承元のReflectionType|nullが入ってくるので無視する(設定にある？)
        // @phpstan-ignore assign.propertyType
        $this->types = ($propertyType instanceof ReflectionUnionType)
            ? $propertyType->getTypes()
            : [$propertyType];

        $this->value = ($this->initializedStatus === PropertyInitializedStatus::INPUTED)
            ? $inputArguments->getValue($refProperty->name)
            : $refProperty->getValue($vo);

        $this->valueType = TypeHelper::getValueType($this->value);
    }
}
