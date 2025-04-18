<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use ReflectionAttribute;
use ReflectionProperty;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Validators\Validatorable;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use ArrayIterator;

/**
 * 単一のプロパティに対するバリデーション処理を管理するクラス
 */
class FieldValidationManager
{
    /**
     * @param array<int, Validatorable> $validators
     */
    private function __construct(
        private readonly array $validators,
    ) {}

    /**
     * プロパティからFieldValidationManagerを生成する
     * BeforeValidatorとAfterValidatorの属性を取得し、バリデーション処理を初期化する
     *
     * @param ReflectionProperty $property
     * @param BaseField $field
     * @param array<FieldValidator> $fieldValidators
     * @param array<Validatorable> $coreValidators
     */
    public static function createFromProperty(
        ReflectionProperty $property,
        BaseField $field,
        array $fieldValidators = [],
        array $coreValidators = [],
    ): self {

        // 属性から取得したバリデータを追加
        $attributeValidators = AttributeHelper::getAttributeInstances(
            $property,
            Validatorable::class,
            ReflectionAttribute::IS_INSTANCEOF,
        );

        // フィールドバリデータを追加
        $thisFieldValdators =
            empty($fieldValidators)
            ? []
            : array_values(array_filter(
                $fieldValidators,
                fn(FieldValidator $validator): bool => $validator->field === $property->name,
            ));

        $validators = [...$attributeValidators, ...$thisFieldValdators, ...$coreValidators, $field];

        // バリデータをモードによってソート
        usort(
            array: $validators,
            callback: fn(Validatorable $a, Validatorable $b): int => $a->getMode()->getPriority() <=> $b->getMode()->getPriority(),
        );


        return new self(validators: $validators);
    }

    /**
     * ValidatorFunctionWrapHandlerを使用してバリデーション処理を実行する
     *
     * @param PropertyOperator $operator プロパティ操作オブジェクト
     * @return PropertyOperator バリデーション後のプロパティ操作オブジェクト
     */
    public function processValidation(PropertyOperator $operator): PropertyOperator
    {
        if (empty($this->validators)) {
            return $operator;
        }

        // ArrayIteratorに変換してValidatorFunctionWrapHandlerで処理
        $validators = new ArrayIterator($this->validators);
        $handler = new ValidatorFunctionWrapHandler($validators);

        $validatedValue = $handler($operator->value->value);

        // 値が変更された場合は新しいPropertyOperatorを作成
        return ($validatedValue !== $operator->value->value)
            ? $operator->withValue($validatedValue)
            : $operator;
    }
}
