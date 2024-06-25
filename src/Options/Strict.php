<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Options;

use ReflectionClass;
use Akitanabe\PhpValueObject\Options\Allowable;
use Akitanabe\PhpValueObject\Options\NotAllow;
use Akitanabe\PhpValueObject\Attributes\AllowUninitializedProperty;
use Akitanabe\PhpValueObject\Attributes\AllowNoneTypeProperty;
use Akitanabe\PhpValueObject\Attributes\AllowMixedTypeProperty;
use Akitanabe\PhpValueObject\Helpers\AttributeHelper;

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

    public function __construct(ReflectionClass $refClass)
    {
        foreach ([
            AllowUninitializedProperty::class,
            AllowNoneTypeProperty::class,
            AllowMixedTypeProperty::class,
        ] as $attrClassName) {
            $refAttrClass = new ReflectionClass($attrClassName);
            // 先頭の5文字(Allow)を削除して、残った最初の文字を小文字に変換
            $propertyName = lcfirst(substr($refAttrClass->getShortName(), 5));

            $this->{$propertyName} = AttributeHelper::getAttribute(
                $refClass,
                $attrClassName
            )?->newInstance() ?? new NotAllow();
        };
    }
}
