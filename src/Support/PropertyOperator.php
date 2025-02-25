<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Support;

use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Enums\PropertyInitializedStatus;
use Akitanabe\PhpValueObject\Enums\PropertyValueType;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Helpers\TypeHelper;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Validation\Validatable;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;
use TypeError;

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

    /**
     * プロパティが未初期化状態か
     */
    public function isUninitialized(): bool
    {
        return $this->initializedStatus === PropertyInitializedStatus::UNINITIALIZED;
    }

    /**
     * プロパティの型をチェック
     * @template T of object
     * @param ReflectionClass<T> $refClass
     * @param Strict $strict
     * 
     * @return void
     * @throws TypeError
     */
    public function checkPropertyType(ReflectionClass $refClass, Strict $strict): void
    {
        TypeHelper::checkType($refClass, $strict, $this);
    }

    /**
     * プロパティに値を設定
     */
    public function setPropertyValue(): void
    {
        $this->refProperty->setValue($this->vo, $this->value);
    }

    /**
     * プロパティに設定されているAttributeからバリデーションを実行
     *
     * @throws ValidationException
     */
    public function validatePropertyValue(): void
    {
        $attributes = $this->refProperty->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);

        if (count($attributes) === 0) {
            return;
        }

        $value = $this->refProperty->getValue($this->vo);

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance->validate($value) === false) {
                throw new ValidationException($attributeInstance, $this->refProperty);
            }
        }
    }
}
