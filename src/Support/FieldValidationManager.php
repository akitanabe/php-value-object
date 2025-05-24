<?php

declare(strict_types=1);

namespace PhSculptis\Support;

use PhSculptis\Core\ValidatorDefinitions;
use PhSculptis\Core\Validators\InitializationStateValidator;
use PhSculptis\Core\Validators\MixedTypeValidator;
use PhSculptis\Core\Validators\NoneTypeValidator;
use PhSculptis\Core\Validators\PrimitiveTypeValidator;
use PhSculptis\Validators\ValidatorQueue;
use PhSculptis\Fields\BaseField;
use PhSculptis\Core\Validators\Validatorable;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;

/**
 * 単一のプロパティに対するバリデーション処理を管理するクラス
 */
class FieldValidationManager
{
    public function __construct(
        private readonly BaseField $field,
        private readonly FunctionValidatorFactory $functionValidatorFactory,
    ) {}

    /**
     * バリデータクラスを取得する。
     * バリデータの順序はこのメソッドで定義されている。
     * @return array<class-string<Validatorable>>
     */
    private function getValidators(): array
    {
        return [
            InitializationStateValidator::class,
            NoneTypeValidator::class,
            MixedTypeValidator::class,
            ...$this->functionValidatorFactory->getValidators(),
            PrimitiveTypeValidator::class,
            $this->field->getValidator(),
        ];
    }

    /**
     * ValidatorFunctionWrapHandlerを使用してバリデーション処理を実行する
     *
     * @param PropertyOperator $operator プロパティ操作オブジェクト
     * @return PropertyOperator バリデーション後のプロパティ操作オブジェクト
     */
    public function processValidation(
        PropertyOperator $operator,
        ValidatorDefinitions $validatorDefinitions,
    ): PropertyOperator {
        $validatorQueue = new ValidatorQueue($this->getValidators());
        $handler = new ValidatorFunctionWrapHandler($validatorQueue, $validatorDefinitions);

        $validatedValue = $handler($operator->value->value);

        // 値が変更された場合は新しいPropertyOperatorを作成
        return ($validatedValue !== $operator->value->value)
            ? $operator->withValue($validatedValue)
            : $operator;
    }
}
