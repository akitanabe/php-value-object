<?php

declare(strict_types=1);

namespace PhSculptis\Config;

use Attribute;
use PhSculptis\Helpers\AttributeHelper;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class FieldConfig extends BaseConfig
{
    /**
     * 初期化していないプロパティを許可する
     */
    public Allowable $uninitializedProperty;

    /**
     * 型がついていないプロパティを許可する
     */
    public Allowable $noneTypeProperty;

    /**
     * mixed型のプロパティを許可する
     */
    public Allowable $mixedTypeProperty;

    /**
     * @param bool $allowUninitializedProperty 初期化していないプロパティを許可する
     * @param bool $allowNoneTypeProperty 型がついていないプロパティを許可する
     * @param bool $allowMixedTypeProperty mixed型のプロパティを許可する
     *
     */
    public function __construct(
        bool $allowUninitializedProperty = false,
        bool $allowNoneTypeProperty = false,
        bool $allowMixedTypeProperty = false,
    ) {

        parent::initialize([
            "uninitializedProperty" => $allowUninitializedProperty,
            "noneTypeProperty" => $allowNoneTypeProperty,
            "mixedTypeProperty" => $allowMixedTypeProperty,
        ]);
    }

    /**
     *
     * @param ReflectionProperty $refProperty
     * @return FieldConfig
     */
    public static function factory(ReflectionProperty $refProperty): self
    {
        return AttributeHelper::getAttribute($refProperty, self::class)?->newInstance() ?? new self();
    }
}
