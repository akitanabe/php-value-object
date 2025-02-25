<?php

namespace Akitanabe\PhpValueObject\Support;

use Akitanabe\PhpValueObject\BaseValueObject;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

class InputArguments
{
    /**
     * @var array<string|int,mixed>
     */
    private array $inputs;

    /**
     * @template T of object
     * @param ReflectionClass<T> $refClass
     * @param array<string|int,mixed> $args
     */
    public function __construct(ReflectionClass $refClass, array $args)
    {
        $this->inputs = $this->getInputs($refClass, $args);
    }

    /**
     * コンストラクタへの入力値が存在しているか
     */
    public function hasValue(string $name): bool
    {
        return array_key_exists($name, $this->inputs);
    }

    /**
     * コンストラクタへの入力値を取得
     */
    public function getValue(string $name): mixed
    {
        return $this->inputs[$name] ?? null;
    }

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
    private function getInputs(ReflectionClass $refClass, array $args): array
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
     * 子クラスのコンストラクタから引数情報を取得して、
     * 渡された引数を名前付き引数で渡されたように変換する
     *
     * @param array<string|int,mixed> $args
     *
     * @return array<string,mixed>
     */
    private function toNamedArgs(ReflectionMethod $refConstructor, array $args): array
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
