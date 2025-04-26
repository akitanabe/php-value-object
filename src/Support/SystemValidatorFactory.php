<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Validators\PrimitiveTypeValidator;
use PhpValueObject\Validators\PropertyInitializedValidator;
use PhpValueObject\Validators\PropertyTypeValidator;
use PhpValueObject\Validators\Validatorable;

/**
 * システムバリデータを生成するファクトリークラス
 */
class SystemValidatorFactory
{
    /**
     * @var array<Validatorable>
     */
    private array $validators;

    /**
     * @param array<Validatorable> $validators
     */
    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * プロパティのためのシステムバリデータを作成する
     *
     * @param PropertyOperator $propertyOperator
     * @param ModelConfig $modelConfig
     * @param FieldConfig $fieldConfig
     * @return SystemValidatorFactory
     */
    public static function createForProperty(
        PropertyOperator $propertyOperator,
        ModelConfig $modelConfig,
        FieldConfig $fieldConfig,
    ): self {
        $standardValidators = [
            new PropertyInitializedValidator($modelConfig, $fieldConfig, $propertyOperator->metadata),
            new PropertyTypeValidator($modelConfig, $fieldConfig, $propertyOperator->metadata),
            new PrimitiveTypeValidator($propertyOperator->metadata),
        ];

        return new self($standardValidators);
    }

    /**
     * バリデータ配列を取得する
     *
     * @return array<Validatorable>
     */
    public function getValidators(): array
    {
        return $this->validators;
    }
}
