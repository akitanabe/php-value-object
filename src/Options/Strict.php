<?php

declare(strict_types=1);

namespace PhpValueObject\Options;

use PhpValueObject\Attributes\AllowInheritableClass;
use PhpValueObject\Attributes\AllowMixedTypeProperty;
use PhpValueObject\Attributes\AllowNoneTypeProperty;
use PhpValueObject\Attributes\AllowUninitializedProperty;
use PhpValueObject\Helpers\AttributeHelper;
use ReflectionClass;

final class Strict
{
    /**
     * @var AllowUninitializedProperty|NotAllow
     * 初期化していないプロパティを許可する
     */
    public Allowable $uninitializedProperty;

    /**
     * @var AllowNoneTypeProperty|NotAllow
     * 型がついていないプロパティを許可する
     */
    public Allowable $noneTypeProperty;

    /**
     * @var AllowMixedTypeProperty|NotAllow
     * mixed型のプロパティを許可する
     */
    public Allowable $mixedTypeProperty;

    /**
     * @var AllowInheritableClass|NotAllow
     * 継承可能クラスを許可する
     * (finalキーワードをつけていないクラスを許可する)
     */
    public Allowable $inheritableClass;

    /**
     * @template T of object
     * @param ReflectionClass<T> $refClass
     */
    public function __construct(ReflectionClass $refClass)
    {
        foreach ([
            AllowUninitializedProperty::class,
            AllowNoneTypeProperty::class,
            AllowMixedTypeProperty::class,
            AllowInheritableClass::class,
        ] as $attrClassName) {
            $refAttrClass = new ReflectionClass($attrClassName);
            // 先頭の5文字(Allow)を削除して、残った最初の文字を小文字に変換
            $propertyName = lcfirst(substr($refAttrClass->getShortName(), 5));

            $this->{$propertyName} = AttributeHelper::getAttribute(
                $refClass,
                $attrClassName,
            )?->newInstance() ?? new NotAllow();
        }
    }
}
