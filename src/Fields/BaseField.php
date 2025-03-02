<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;

/**
 * @template T of object
 * @phpstan-type Callable callable-string|class-string<T>|array{string|object, string}|Closure|null
 */
abstract class BaseField
{
    protected readonly ?Closure $factoryFn;

    /**
     * @param Callable $defaultFactory　default値が存在していたらUnexpectedValueExceptionが投げられます
     * @param string|null $alias フィールド名のエイリアス。指定された場合、エイリアス名で入力値を取得します
     */
    public function __construct(
        string|array|Closure|null $defaultFactory = null,
        public readonly ?string $alias = null,
    ) {
        $this->factoryFn = $defaultFactory ? FieldsHelper::createFactory($defaultFactory) : null;
    }

    /**
     * @param array<string|int, mixed> $data
     *
     * @return mixed
     */
    public function defaultFactory(array $data): mixed
    {
        if ($this->factoryFn === null) {
            return null;
        }

        return ($this->factoryFn)($data);
    }

    public function hasDefaultFactory(): bool
    {
        return $this->factoryFn !== null;
    }
}
