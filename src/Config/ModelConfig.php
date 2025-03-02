<?php

declare(strict_types=1);

namespace PhpValueObject\Config;

use Attribute;
use PhpValueObject\Helpers\AttributeHelper;
use ReflectionClass;
use PhpValueObject\BaseModel;

#[Attribute(Attribute::TARGET_CLASS)]
final class ModelConfig extends BaseConfig
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
     * 継承可能クラスを許可する
     * (finalキーワードをつけていないクラスを許可する)
     */
    public Allowable $inheritableClass;

    /**
     * @param bool $allowUninitializedProperty 初期化していないプロパティを許可する
     * @param bool $allowNoneTypeProperty 型がついていないプロパティを許可する
     * @param bool $allowMixedTypeProperty mixed型のプロパティを許可する
     * @param bool $allowInheritableClass 継承可能クラスを許可する
     *
     */
    public function __construct(
        bool $allowUninitializedProperty = false,
        bool $allowNoneTypeProperty = false,
        bool $allowMixedTypeProperty = false,
        bool $allowInheritableClass = true,
    ) {
        parent::initialize(
            [
                "uninitializedProperty" => $allowUninitializedProperty,
                "noneTypeProperty" => $allowNoneTypeProperty,
                "mixedTypeProperty" => $allowMixedTypeProperty,
                "inheritableClass" => $allowInheritableClass,
            ],
        );
    }

    /**
     *
     * @param ReflectionClass<BaseModel> $refClass
     * @return ModelConfig
     */
    public static function factory(ReflectionClass $refClass): self
    {
        return AttributeHelper::getAttribute($refClass, self::class)?->newInstance() ?? new self();
    }
}
