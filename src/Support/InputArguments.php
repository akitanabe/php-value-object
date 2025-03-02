<?php

namespace PhpValueObject\Support;

class InputArguments
{

    /**
     * @param array<string|int, mixed> $inputs
     */
    public function __construct(
        public readonly array $inputs,
    ) {
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
