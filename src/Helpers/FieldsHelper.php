<?php

declare(strict_types=1);

namespace PhpValueObject\Helpers;

use Closure;
use InvalidArgumentException;
use PhpValueObject\Fields\BaseField;
use PhpValueObject\Fields\Field;
use PhpValueObject\Validation\FieldValidator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use PhpValueObject\BaseModel;

class FieldsHelper
{
    /**
     * 引数をそのまま返す関数
     *
     * @template T
     * @param T $value
     * @return T
     */
    public static function identity(mixed $value): mixed
    {
        return $value;
    }

    /**
     * ファクトリ関数生成
     *
     * @template T of object
     * @param callable-string|class-string<T>|array{string|object, string}|Closure $factory
     * @return Closure
     */
    public static function createFactory(string|array|Closure $factory): Closure
    {
        $fn = match (true) {
            // クラス名の場合はインスタンスを生成
            (is_string($factory) && class_exists($factory)) => new $factory(),
            default => $factory,
        };

        if (is_callable($fn) === false) {
            throw new InvalidArgumentException('Factory must be a callable');
        }

        return $fn(...);
    }

    /**
     * プロパティに設定されているBaseField継承クラスを生成する
     * 設定がない場合はFieldクラスを生成する
     * @param ReflectionProperty $refProperty
     * @return BaseField
     *
     */
    public static function createField(ReflectionProperty $refProperty): BaseField
    {
        return AttributeHelper::getAttribute(
            $refProperty,
            BaseField::class,
            ReflectionAttribute::IS_INSTANCEOF,
        )?->newInstance() ?? new Field();
    }

    /**
     * FieldValidatorのリストを取得する
     *
     * @template T of BaseModel
     * @param ReflectionClass<T> $refClass
     * @param T $model
     *
     * @return FieldValidator[]
     */
    public static function getFieldValidators(ReflectionClass $refClass, BaseModel $model): array
    {
        // バリデーションメソッドをFieldValidatorに入力する
        $setValidator = function (FieldValidator $fieldValidator, ReflectionMethod $refMethod) use (
            $model
        ): FieldValidator {
            $methodName = $refMethod->getName();
            $validator = $model->{$methodName}(...);

            $fieldValidator->setValidator($validator);

            return $fieldValidator;
        };

        return array_reduce(
            $refClass->getMethods(),
            function (array $carry, ReflectionMethod $refMethod) use ($setValidator): array {
                $fieldValidators = AttributeHelper::getAttributeInstances($refMethod, FieldValidator::class);

                return [
                    ...$carry,
                    ...array_map(
                        fn(FieldValidator $fieldValidator): FieldValidator
                        => $setValidator($fieldValidator, $refMethod),
                        $fieldValidators,
                    ),
                ];
            },
            [],
        );

    }
}
