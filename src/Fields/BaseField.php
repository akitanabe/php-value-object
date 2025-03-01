<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;

abstract class BaseField
{
    protected readonly ?Closure $factoryFn;

    /**
     * @template T of object
     * @param callable-string|class-string<T>|array{string|object, string}|Closure|null $factory
     *
     */
    public function __construct(
        string|array|Closure|null $factory = null,
    ) {
        $this->factoryFn = $factory ? FieldsHelper::createFactory($factory) : null;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function factory(mixed $value): mixed
    {
        if ($this->factoryFn === null) {
            return FieldsHelper::identity($value);
        }

        return ($this->factoryFn)($value);
    }

    public function hasFactory(): bool
    {
        return $this->factoryFn !== null;
    }
}
