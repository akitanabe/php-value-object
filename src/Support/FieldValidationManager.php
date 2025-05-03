<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use ReflectionProperty;
use SplQueue;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Core\Validators\Validatorable;
use PhpValueObject\Helpers\ValidatorHelper;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

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
    ) {
    }

    /**
     * プロパティからFieldValidationManagerを生成する
     * BeforeValidatorとAfterValidatorの属性を取得し、バリデーション処理を初期化する
     *
     * @param ReflectionProperty $property
     * @param BaseField $field
     * @param FunctionValidatorFactory|null $functionValidatorFactory 関数型バリデータを提供するファクトリ
     * @param SystemValidatorFactory|null $systemValidators
     */
    public static function createFromProperty(
        ReflectionProperty $property,
        BaseField $field,
        ?FunctionValidatorFactory $functionValidatorFactory = null,
        ?SystemValidatorFactory $systemValidators = null,
    ): self {
        // FunctionValidatorFactoryから関数バリデータを取得
        // (フィールドバリデータと属性バリデータの両方を含む)
        $functionValidators = $functionValidatorFactory?->createValidators() ?? [];

        // システムバリデータを pre と standard に分けて取得
        $preSystemValidators = $systemValidators?->getPreValidators() ?? [];
        $standardSystemValidators = $systemValidators?->getStandardValidators() ?? [];

        // バリデータの順序: preシステム → 関数型バリデータ → standardシステム
        $validators = [
            ...$preSystemValidators,       // 1. Pre System Validators
            ...$functionValidators,        // 2. Function Validators (User Defined, includes field and attribute validators)
            ...$standardSystemValidators,  // 3. Standard System Validators
        ];

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

        // ValidatorHelperを使用してSplQueueに変換
        $validators = ValidatorHelper::createValidatorQueue($this->validators);
        $handler = new ValidatorFunctionWrapHandler($validators);

        $validatedValue = $handler($operator->value->value);

        // 値が変更された場合は新しいPropertyOperatorを作成
        return ($validatedValue !== $operator->value->value)
            ? $operator->withValue($validatedValue)
            : $operator;
    }
}
