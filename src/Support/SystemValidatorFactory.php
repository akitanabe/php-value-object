<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Validators\PrimitiveTypeValidator;
use PhpValueObject\Validators\InitializationStateValidator;
use PhpValueObject\Validators\PropertyTypeValidator;
use PhpValueObject\Validators\Validatorable;

/**
 * システムバリデータを生成するファクトリークラス
 * preValidator (ユーザー定義バリデータより前) と standardValidator (ユーザー定義バリデータより後) を管理する
 */
class SystemValidatorFactory
{
    /**
     * @var array<Validatorable> ユーザー定義バリデータより前に実行されるシステムバリデータ
     */
    private array $preValidators;

    /**
     * @var array<Validatorable> ユーザー定義バリデータより後に実行されるシステムバリデータ
     */
    private array $standardValidators;

    /**
     * @param array<Validatorable> $preValidators
     * @param array<Validatorable> $standardValidators
     */
    public function __construct(array $preValidators, array $standardValidators)
    {
        $this->preValidators = $preValidators;
        $this->standardValidators = $standardValidators;
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
        BaseField $field,
    ): self {
        // preValidator として PropertyInitializedValidator と PropertyTypeValidator を指定
        $preValidators = [
            new InitializationStateValidator($modelConfig, $fieldConfig, $propertyOperator->metadata),
            new PropertyTypeValidator($modelConfig, $fieldConfig, $propertyOperator->metadata),
        ];

        // standardValidator として PrimitiveTypeValidator と BaseField のバリデータを指定
        $standardValidators = [new PrimitiveTypeValidator($propertyOperator->metadata), $field->getValidator(),];

        return new self($preValidators, $standardValidators);
    }

    /**
     * preValidator の配列を取得する
     *
     * @return array<Validatorable>
     */
    public function getPreValidators(): array
    {
        return $this->preValidators;
    }

    /**
     * standardValidator の配列を取得する
     *
     * @return array<Validatorable>
     */
    public function getStandardValidators(): array
    {
        return $this->standardValidators;
    }
}
