<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Support\PropertyOperator;

/**
 * フィールドの基底クラス
 * @phpstan-type default_factory callable-string|class-string|array{string|object, string}|Closure
 */
abstract class BaseField
{
    /**
     *
     * @param default_factory|null $defaultFactory default値の生成関数。default値が存在していたらUnexpectedValueExceptionが投げられます
     * @param string|null $alias フィールド名のエイリアス。指定された場合、エイリアス名で入力値を取得します
     */
    public function __construct(
        protected string|array|Closure|null $defaultFactory = null,
        public readonly ?string $alias = null,
    ) {}

    /**
     * @param array<string|int, mixed> $data
     *
     * @return mixed
     */
    public function defaultFactory(array $data): mixed
    {
        if ($this->defaultFactory === null) {
            return null;
        }

        $factoryFn = FieldsHelper::createFactory($this->defaultFactory);

        return $factoryFn($data);
    }

    public function hasDefaultFactory(): bool
    {
        return $this->defaultFactory !== null;
    }

    /**
     * バリデーション
     *
     * @throws ValidationException
     */
    abstract public function validate(PropertyOperator $propertyOperator): void;
}
