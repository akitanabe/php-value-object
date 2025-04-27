<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use InvalidArgumentException;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Validators\FieldValidator;
use ReflectionClass;
use ReflectionMethod;

/**
 * FieldValidator インスタンスを生成・管理するファクトリクラス
 */
final class FieldValidatorFactory
{
    /**
     * @var array<string, FieldValidator[]> フィールド名をキーとした FieldValidator の配列
     */
    private readonly array $validatorsByField;

    /**
     * @param array<string, FieldValidator[]> $validatorsByField
     */
    private function __construct(array $validatorsByField)
    {
        $this->validatorsByField = $validatorsByField;
    }

    /**
     * ReflectionClass から FieldValidatorFactory インスタンスを生成する
     *
     * @template T of object
     * @param ReflectionClass<T> $refClass 対象クラスの Reflection
     * @return self
     * @throws InvalidArgumentException メソッドが static でない場合
     */
    public static function createFromClass(ReflectionClass $refClass): self
    {
        $className = $refClass->name;
        $validatorsByField = [];

        // バリデーションメソッドをFieldValidatorに入力する内部関数
        $setValidator = function (FieldValidator $fieldValidator, ReflectionMethod $refMethod) use (
            $className
        ): FieldValidator {
            $methodName = $refMethod->getName();

            // staticメソッドであることを確認
            if ($refMethod->isStatic() === false) {
                throw new InvalidArgumentException(
                    "Method {$className}::{$methodName} must be static for use with FieldValidator",
                );
            }

            $validator = [$className, $methodName];
            $fieldValidator->setValidator($validator);

            return $fieldValidator;
        };

        foreach ($refClass->getMethods() as $refMethod) {
            $fieldValidators = AttributeHelper::getAttributeInstances($refMethod, FieldValidator::class);

            foreach ($fieldValidators as $fieldValidator) {
                $initializedValidator = $setValidator($fieldValidator, $refMethod);
                $fieldName = $initializedValidator->field;

                // フィールド名ごとにバリデータをグループ化
                if (isset($validatorsByField[$fieldName]) === false) {
                    $validatorsByField[$fieldName] = [];
                }
                $validatorsByField[$fieldName][] = $initializedValidator;
            }
        }

        return new self($validatorsByField);
    }

    /**
     * 指定されたフィールド名の FieldValidator 配列を取得する
     *
     * @param string $fieldName フィールド名
     * @return FieldValidator[] 対応する FieldValidator の配列 (存在しない場合は空配列)
     */
    public function getValidatorsForField(string $fieldName): array
    {
        return $this->validatorsByField[$fieldName] ?? [];
    }
}
