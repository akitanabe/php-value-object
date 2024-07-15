<?php

namespace Akitanabe\PhpValueObject\Helpers;

use Akitanabe\PhpValueObject\BaseValueObject;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class ArgumentsHelper
{
    /**
     * コンストラクタへの入力値を取得
     * オーバーライドされていない場合は、そのまま引数を返す
     * 
     * @template T of object
     * @param ReflectionClass<T> $refClass
     * @param array<string|int,mixed> $args
     * 
     * @return array<string|int,mixed>
     */
    static public function getInputArgs(ReflectionClass $refClass, array $args): array
    {
        $refConstructor = $refClass->getConstructor();

        // コンストラクタがオーバーライドされている場合、子クラスのコンストラクタパラメータから引数を設定する
        return (
            isset($refConstructor)
            && $refConstructor->getDeclaringClass()->name !== BaseValueObject::class
        )
            ? self::toNamedArgs($refConstructor, $args)
            : $args;
    }

    /**
     * 
     * 子クラスのコンストラクタから引数情報を取得して、
     * 渡された引数を名前付き引数で渡されたように変換する
     * 
     * @param ReflectionMethod $refConstructor
     * @param array<string|int,mixed> $args
     * 
     * @return array<string,mixed>
     * 
     */
    static private function toNamedArgs(ReflectionMethod $refConstructor, array $args): array
    {
        $overrideArgs = array_reduce(
            $refConstructor->getParameters(),
            function (array $newArgs, ReflectionParameter $param) use ($args) {
                $paramName = $param->getName();
                $paramPosition = $param->getPosition();

                // 渡された引数が名前付き引数か不明なので、引数の名前と位置で取得
                if (array_key_exists($paramPosition, $args)) {
                    $newArgs[$paramName] = $args[$paramPosition];
                } elseif (array_key_exists($paramName, $args)) {
                    $newArgs[$paramName] = $args[$paramName];
                    // デフォルト値が存在した場合は取得
                } elseif ($param->isDefaultValueAvailable()) {
                    $newArgs[$paramName] = $param->getDefaultValue();
                }

                return $newArgs;
            },
            [],
        );

        // 渡された引数のうち、子クラスのコンストラクタに定義されていない引数を取得
        // 名前付き引数しか対応しない
        foreach ($args as $key => $value) {
            if (
                is_int($key) === false
                && array_key_exists($key, $overrideArgs) === false
            ) {
                $overrideArgs[$key] = $value;
            }
        }

        return $overrideArgs;
    }
}
