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
    ) {
    }

    /**
     * プロパティからFieldValidationManagerを生成する
     * BeforeValidatorとAfterValidatorの属性を取得し、バリデーション処理を初期化する
     *
     * @param ReflectionProperty $property
     * @param array<FieldValidator> $fieldValidators
     */
    public static function createFromProperty(
        ReflectionProperty $property,
        BaseField $field,
        array $fieldValidators = []
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

        $validators = [
            ...$attributeValidators,
            ...$thisFieldValdators,
            $field,
        ];

        // バリデータをモードによってソート
        usort($validators, function (Validatorable $a, Validatorable $b): int {
            $modes = [
                'before' => 1,
                'field' => 2,
                'after' => 3
            ];
            return $modes[$a->getMode()] <=> $modes[$b->getMode()];
        });

        return new self(validators: $validators);
    }

    /**
     * ValidatorFunctionWrapHandlerを使用してバリデーション処理を実行する
     *
     * @param mixed $value 検証する値
     * @return mixed 検証結果の値
     */
    public function processValidation(mixed $value): mixed
    {
        if (empty($this->validators)) {
            return $value;
        }

        // ArrayIteratorに変換してValidatorFunctionWrapHandlerで処理
        $validators = new ArrayIterator($this->validators);

        $handler = new ValidatorFunctionWrapHandler($validators);
        return $handler($value);
    }
}
