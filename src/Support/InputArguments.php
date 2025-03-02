<?php

namespace PhpValueObject\Support;

class InputArguments
{
    /**
     * @var array<string|int, mixed>
     */
    public readonly array $inputs;

    /**
     * @param array<string|int, mixed> $args
     */
    public function __construct(array $args)
    {
        $this->inputs = $args;
    }

    /**
     * コンストラクタへの入力値が存在しているか
     */
    public function hasValue(string $name, ?string $alias = null): bool
    {
        $key = $alias ?? $name;
        return array_key_exists($key, $this->inputs);
    }

    /**
     * コンストラクタへの入力値を取得
     */
    public function getValue(string $name, ?string $alias = null): mixed
    {
        $key = $alias ?? $name;
        return $this->inputs[$key] ?? null;
    }

}
