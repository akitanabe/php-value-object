<?php

declare(strict_types=1);

namespace PhSculptis\Core\Validators;

use PhSculptis\Core\ValidatorDefinitions;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use InvalidArgumentException;

/**
 * バリデータビルドのための共通実装を提供するトレイト
 */
trait ValidatorBuildTrait
{
    /**
     * ValidatorDefinitionsからバリデータを構築する
     *
     * @param ValidatorDefinitions $definitions バリデータ定義
     * @return Validatorable 構築されたバリデータインスタンス
     */
    public static function build(ValidatorDefinitions $definitions): Validatorable
    {
        $reflection = new ReflectionClass(self::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor?->getParameters() ?? [];

        if ($constructor === null || empty($parameters)) {
            // @phpstan-ignore arguments.count (コンストラクタ引数が存在しないのはチェック済み)
            return new self();
        }

        $args = array_map(
            fn(ReflectionParameter $parameter): ?object => self::resolveParameter($parameter, $definitions),
            $parameters,
        );

        /**
         * @phpstan-ignore new.noConstructor, arguments.count, argument.type
         * (コンストラクタが存在しないのはチェック済み, $paramertersの数は$constructorの引数の数と一致する, 引数の型は解決済み)
         * */
        return new self(...$args);
    }

    /**
     * コンストラクタパラメータの値をValidatorDefinitionsから解決する
     *
     * @param ReflectionParameter $parameter 解決するパラメータ
     * @param ValidatorDefinitions $definitions バリデータ定義
     * @return ?object 解決された引数の値
     * @throws InvalidArgumentException パラメータを解決できない場合
     */
    private static function resolveParameter(ReflectionParameter $parameter, ValidatorDefinitions $definitions): ?object
    {
        $paramType = $parameter->getType();

        // 型定義から解決できないためパラメータの型情報がない場合やUnion型や交差型の場合は例外を投げる
        if ($paramType === null || ($paramType instanceof ReflectionNamedType) === false) {
            throw new InvalidArgumentException(
                "Cannot resolve constructor parameter '{$parameter->getName()}' without type hint for validator " . static::class,
            );
        }

        /**
         * @var class-string $typeName
         */
        $typeName = $paramType->getName();

        $definition = $definitions->get($typeName);
        if ($definition !== null) {
            return $definition;
        }

        // 定義が見つからない場合はデフォルト値を返すか例外を投げる
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new InvalidArgumentException(
            "Cannot resolve constructor parameter of type '{$typeName}' for validator " . static::class,
        );
    }
}
