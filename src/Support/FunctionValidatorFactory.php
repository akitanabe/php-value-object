<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Core\Validators\FunctionValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Validators\FunctionalValidator;
use PhpValueObject\Validators\ValidatorMode;
use PhpValueObject\Validators\ValidatorCallable;
use ReflectionAttribute;
use ReflectionProperty;

/**
 * FieldValidatorとFunctionalValidatorからFunctionValidatorを生成するファクトリクラス
 */
final class FunctionValidatorFactory
{
    /**
     * @param array<FieldValidator> $fieldValidators フィールドバリデータの配列
     * @param array<FunctionalValidator> $functionalValidators 関数型バリデータの配列
     */
    public function __construct(
        private readonly array $fieldValidators,
        private readonly array $functionalValidators,
    ) {}

    /**
     * FieldValidatorStorageからFunctionValidatorFactoryを作成する
     *
     * @param FieldValidatorStorage $validatorStorage バリデータストレージ
     * @param ReflectionProperty $property バリデーション対象のプロパティ
     * @return self
     */
    public static function createFromStorage(
        FieldValidatorStorage $validatorStorage,
        ReflectionProperty $property,
    ): self {
        // プロパティに関連付けられたすべてのFieldValidatorを取得
        $fieldValidators = $validatorStorage->getValidatorsForProperty($property);

        // プロパティから直接アトリビュートとして指定されたValidatorCallableを取得
        $functionalValidators = self::getFunctionalValidators($property);

        return new self($fieldValidators, $functionalValidators);
    }

    /**
     * FuncationalValidatorを取得する
     * @param ReflectionProperty $property
     * @return FunctionalValidator[]
     */
    public static function getFunctionalValidators(ReflectionProperty $property,): array
    {
        // プロパティから直接アトリビュートとして指定されたValidatorCallableを取得
        return AttributeHelper::getAttributeInstances(
            $property,
            FunctionalValidator::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );
    }

    /**
     * 登録されているバリデータからFunctionValidatorを作成する
     *
     * @return array<FunctionValidator> 生成されたFunctionValidatorの配列
     */
    public function createValidators(): array
    {
        return array_map(
            static fn(ValidatorCallable $validator): FunctionValidator => match ($validator->getMode()) {
                ValidatorMode::BEFORE => new FunctionBeforeValidator($validator->resolveValidator()),
                ValidatorMode::AFTER => new FunctionAfterValidator($validator->resolveValidator()),
                ValidatorMode::WRAP => new FunctionWrapValidator($validator->resolveValidator()),
                ValidatorMode::PLAIN => new FunctionPlainValidator($validator->resolveValidator()),
            },
            [...$this->fieldValidators, ...$this->functionalValidators,],
        );
    }
}
