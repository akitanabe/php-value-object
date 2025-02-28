<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;

abstract class BaseField
{
    protected Closure $factory;

    /**
     * @template T of object
     * @param callable-string|class-string<T>|array{string|object, string}|Closure $factory
     *
     */
    public function __construct(
        string|array|Closure $factory = [FieldsHelper::class, 'identity'],
    ) {

        $this->factory = FieldsHelper::createFactory($factory);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    public function factory(mixed $value): mixed
    {
        return ($this->factory)($value);
    }
}
