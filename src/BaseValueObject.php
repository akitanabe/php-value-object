<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionAttribute;
use ReflectionProperty;
use TypeError;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Helpers\TypeHelper;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Validation\Validatable;
use Akitanabe\PhpValueObject\Concerns\Assert;
use Akitanabe\PhpValueObject\Dto\PropertyDto;

abstract class BaseValueObject
{
    use Assert;
    private Strict $strict;

    /**
     * @param mixed[] $args
     * 
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    public function __construct(...$args)
    {
        $refClass = new ReflectionClass($this);

        $strict = new Strict($refClass);
        $this->strict = $strict;

        // finalクラスであることを強制(Attributeが設定されていなければ継承不可)
        $this->assertInheritableClass($refClass, $strict);

        $refConstructor = $refClass->getConstructor();

        // コンストラクタがオーバーライドされている場合、子クラスのコンストラクタパラメータから引数を設定する
        if ($refConstructor->getDeclaringClass()->name !== self::class) {
            $args = $this->toNamedArgs($refConstructor, $args);
        }

        foreach ($refClass->getProperties() as $property) {
            $propertyDto = new PropertyDto($this, $property, $args);

            if ($this->assertUninitializedPropertyOrSkip($refClass, $strict, $propertyDto)) {
                continue;
            }

            TypeHelper::checkType(
                $refClass,
                $strict,
                $propertyDto,
            );

            $property->setValue(
                $this,
                $propertyDto->value,
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
     * @return array<string, mixed>
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
     * プロパティに設定されているAttributeからバリデーションを実行
     * 
     * @param ReflectionProperty $refProp
     * @return void
     * 
     * @throws ValidationException
     */
    private function validateProperty(ReflectionProperty $refProp): void
    {
        $attributes = $refProp->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF);
        $value = $refProp->getValue($this);

        foreach ($attributes as $attribute) {
            $attributeInstance = $attribute->newInstance();

            if ($attributeInstance->validate($value, $refProp) === false) {
                throw new ValidationException(
                    $attributeInstance,
                    $refProp,
                );
            }
        }
    }

    /**
     * クローン時にはimmutableにするためオブジェクトはcloneする
     */
    public function __clone()
    {
        foreach (get_object_vars($this) as $prop => $value) {
            if (is_object($value)) {
                $this->{$prop} = clone $value;
            }
        };
    }
}
