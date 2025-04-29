<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use InvalidArgumentException;
use PhpValueObject\Helpers\AttributeHelper;
use PhpValueObject\Validators\FieldValidator;
use PhpValueObject\Core\Validators\FunctionValidator;
use ReflectionClass;

/**
 * FieldValidatorからFunctionValidator インスタンスを生成・管理するファクトリクラス
 */
final class FieldValidatorFactory
{
    /**
     * @param array<string, FunctionValidator[]> $validatorsByField
     */
    private function __construct(private readonly array $validatorsByField)
    {
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

        foreach ($refClass->getMethods() as $refMethod) {
            $fieldValidatorAttrs = AttributeHelper::getAttributeInstances($refMethod, FieldValidator::class);

            foreach ($fieldValidatorAttrs as $fieldValidatorAttr) {
                $methodName = $refMethod->getName();

                // staticメソッドであることを確認
                if ($refMethod->isStatic() === false) {
                    throw new InvalidArgumentException(
                        "Method {$className}::{$methodName} must be static for use with FieldValidator",
                    );
                }

                $validatorCallable = [$className, $methodName];

                // FieldValidator Attribute から FunctionValidator を生成
                $functionValidator = $fieldValidatorAttr->getValidator($validatorCallable);

                // フィールド名ごとに FunctionValidator をグループ化
                $fieldName = $fieldValidatorAttr->field;
                if (isset($validatorsByField[$fieldName]) === false) {
                    $validatorsByField[$fieldName] = [];
                }
                $validatorsByField[$fieldName][] = $functionValidator;
            }
        }

        return new self($validatorsByField);
    }

    /**
     * 指定されたフィールド名の FunctionValidator 配列を取得する
     *
     * @param string $fieldName フィールド名
     * @return FunctionValidator[] 対応する FunctionValidator の配列 (存在しない場合は空配列)
     */
    public function getValidatorsForField(string $fieldName): array
    {
        return $this->validatorsByField[$fieldName] ?? [];
    }
}
