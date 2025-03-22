<?php

declare(strict_types=1);

namespace PhpValueObject\Validation;

use ReflectionProperty;
use PhpValueObject\Helpers\AttributeHelper;

/**
 * 単一のプロパティに対するバリデーション処理を管理するクラス
 */
class FieldValidationManager
{
    /** @var Validatorable[] */
    private readonly array $beforeValidators;

    /** @var Validatorable[] */
    private readonly array $afterValidators;

    /**
     * @param Validatorable[] $beforeValidators
     * @param Validatorable[] $afterValidators
     */
    private function __construct(
        array $beforeValidators,
        array $afterValidators
    ) {
        $this->beforeValidators = $beforeValidators;
        $this->afterValidators = $afterValidators;
    }

    /**
     * プロパティからFieldValidationManagerを生成する
     * BeforeValidatorとAfterValidatorの属性を取得し、バリデーション処理を初期化する
     */
    public static function createFromProperty(ReflectionProperty $property): self
    {
        return new self(
            AttributeHelper::getAttributeInstances($property, BeforeValidator::class),
            AttributeHelper::getAttributeInstances($property, AfterValidator::class)
        );
    }

    /**
     * 
     * @param Validatorable[] $validators
     * @param mixed $value
     * @return mixed
     */
    private function processValidation(array $validators, mixed $value): mixed
    {
        return array_reduce(
            $validators,
            fn(mixed $value, Validatorable $validator): mixed => $validator->validate($value),
            $value
        );
    }

    /**
     * BeforeValidatorによるバリデーション処理を実行する
     * PropertyOperator::valueへの入力前に実行される
     */
    public function processBeforeValidation(mixed $value): mixed
    {
        return $this->processValidation($this->beforeValidators, $value);
    }

    /**
     * AfterValidatorによるバリデーション処理を実行する
     * setPropertyValueの前に実行される
     */
    public function processAfterValidation(mixed $value): mixed
    {
        return $this->processValidation($this->afterValidators, $value);
    }
}
