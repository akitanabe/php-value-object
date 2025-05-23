<?php

declare(strict_types=1);

namespace PhSculptis\Support;

use PhSculptis\Core\Definitions\FunctionValidatorDefinition;
use PhSculptis\Core\Validators\FunctionValidator;
use PhSculptis\Helpers\AttributeHelper;
use PhSculptis\Validators\FieldValidator;
use PhSculptis\Validators\FunctionalValidator;
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
        private readonly array $fieldValidators = [],
        private readonly array $functionalValidators = [],
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
     * プロパティに設定されているFuncationalValidatorを取得する
     * @param ReflectionProperty $property
     * @return FunctionalValidator[]
     */
    public static function getFunctionalValidators(ReflectionProperty $property): array
    {
        // プロパティから直接アトリビュートとして指定されたValidatorCallableを取得
        return AttributeHelper::getAttributeInstances(
            $property,
            FunctionalValidator::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );
    }

    /**
     * @return class-string<FunctionValidator>[]
     */
    public function getValidators(): array
    {
        $count = count([...$this->fieldValidators, ...$this->functionalValidators,]);

        return array_fill(0, $count, FunctionValidator::class);
    }

    public function createDefinition(): FunctionValidatorDefinition
    {
        return new FunctionValidatorDefinition([...$this->fieldValidators, ...$this->functionalValidators,]);
    }
}
