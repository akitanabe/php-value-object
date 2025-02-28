<?php

declare(strict_types=1);

namespace PhpValueObject\Helpers;

use Closure;
use InvalidArgumentException;

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
}
