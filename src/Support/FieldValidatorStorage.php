<?php

declare(strict_types=1);

namespace PhSculptis\Support;

use InvalidArgumentException;
use PhSculptis\Helpers\AttributeHelper;
use PhSculptis\Validators\FieldValidator;
use ReflectionClass;
use ReflectionProperty;
use SplObjectStorage;
use ReflectionException;

/**
 * ReflectionPropertyとFieldValidatorの関連付けを管理するストレージクラス
 * @extends SplObjectStorage<ReflectionProperty, FieldValidator[]>
 */
final class FieldValidatorStorage extends SplObjectStorage
{
    /**
     * nameプロパティのハッシュ値を取得する
     * @param ReflectionProperty $property
     */
    public function getHash(object $property): string
    {
        return $property->name;
    }

    /**
     * ReflectionClassからFieldValidatorStorageインスタンスを生成する
     *
     * @template T of object
     * @param ReflectionClass<T> $refClass 対象クラスのReflection
     * @return self
     * @throws InvalidArgumentException メソッドがstaticでない場合
     */
    public static function createFromClass(ReflectionClass $refClass): self
    {
        $instance = new self();
        $className = $refClass->getName();

        foreach ($refClass->getMethods() as $refMethod) {
            $fieldValidators = AttributeHelper::getAttributeInstances($refMethod, FieldValidator::class);

            if (empty($fieldValidators)) {
                continue;
            }

            $methodName = $refMethod->name;

            // staticメソッドであることを確認
            if ($refMethod->isStatic() === false) {
                throw new InvalidArgumentException(
                    "Method {$className}::{$methodName} must be static for use with FieldValidator",
                );
            }

            $validatorCallable = [$className, $methodName];

            // 各FieldValidatorアトリビュートに対して処理
            foreach ($fieldValidators as $fieldValidator) {
                $fieldName = $fieldValidator->field;

                // 指定されたフィールド名に対応するReflectionPropertyを探す
                try {
                    $refProperty = $refClass->getProperty($fieldName);
                } catch (ReflectionException $e) {
                    throw new InvalidArgumentException(
                        "Property '{$fieldName}' referenced in {$className}::{$methodName} does not exist",
                    );
                }

                // FieldValidatorにcallableをセット
                $fieldValidator->setCallable($validatorCallable);

                // ReflectionPropertyとFieldValidatorを関連付ける
                $instance->addValidator($refProperty, $fieldValidator);
            }
        }

        return $instance;
    }

    /**
     * ReflectionPropertyに対応するバリデータを追加する
     *
     * @param ReflectionProperty $property 対象プロパティのReflection
     * @param FieldValidator $validator 追加するFieldValidatorインスタンス
     * @return void
     */
    public function addValidator(ReflectionProperty $property, FieldValidator $validator): void
    {
        $validators = [];

        if ($this->offsetExists($property)) {
            $validators = $this->offsetGet($property);
        }

        $validators[] = $validator;
        $this->offsetSet($property, $validators);
    }

    /**
     * 指定されたReflectionPropertyに対応するFieldValidatorの配列を取得する
     *
     * @param ReflectionProperty $property 対象プロパティのReflection
     * @return FieldValidator[] 対応するFieldValidatorの配列（存在しない場合は空配列）
     */
    public function getValidatorsForProperty(ReflectionProperty $property): array
    {
        if ($this->offsetExists($property) === false) {
            return [];
        }

        return $this->offsetGet($property);
    }
}
