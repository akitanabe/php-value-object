<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use ReflectionAttribute;
use ReflectionProperty;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Validators\Validatorable;
use PhpValueObject\Validators\FieldValidator;

/**
 * 単一のプロパティに対するバリデーション処理を管理するクラス
 *
 * @phpstan-import-type validator_mode from Validatorable
 */
class FieldValidationManager
{
    private function __construct(
        /** @var Validatorable[] */
        private readonly array $validators,
    ) {
    }

    /**
     * プロパティからFieldValidationManagerを生成する
     * BeforeValidatorとAfterValidatorの属性を取得し、バリデーション処理を初期化する
     *
     * @param ReflectionProperty $property
     * @param array<FieldValidator> $fieldValidators
     *
     */
    public static function createFromProperty(ReflectionProperty $property, array $fieldValidators = []): self
    {


        $validators = [
            ...AttributeHelper::getAttributeInstances(
                $property,
                Validatorable::class,
                ReflectionAttribute::IS_INSTANCEOF,
            ),
            ...array_values(
                array_filter(
                    $fieldValidators,
                    fn(FieldValidator $validator): bool => $validator->field === $property->getName(),
                ),
            ),
        ];

        return new self(validators: $validators);
    }

    /**
     *
     * @param validator_mode $mode
     * @param mixed $value
     * @return mixed
     */
    private function processValidation(string $mode, mixed $value): mixed
    {
        $validators = array_filter(
            $this->validators,
            fn(Validatorable $validator): bool => $validator->getMode() === $mode,
        );

        return array_reduce(
            $validators,
            fn(mixed $value, Validatorable $validator): mixed => $validator->validate($value),
            $value,
        );
    }

    /**
     * BeforeValidatorによるバリデーション処理を実行する
     * PropertyOperator::valueへの入力前に実行される
     */
    public function processBeforeValidation(mixed $value): mixed
    {
        return $this->processValidation('before', $value);
    }

    /**
     * AfterValidatorによるバリデーション処理を実行する
     * setPropertyValueの前に実行される
     */
    public function processAfterValidation(mixed $value): mixed
    {
        return $this->processValidation('after', $value);
    }
}
