<?php

namespace PhpValueObject\Support;

class InputData
{
    /**
     * @param array<string|int, mixed> $data
     */
    public function __construct(
        public readonly array $data,
    ) {
    }

    /**
     * コンストラクタへの入力値が存在しているか
     */
    public function hasValue(string $name, ?string $alias = null): bool
    {
        $key = $alias ?? $name;
        return array_key_exists($key, $this->data);
    }

    /**
     * コンストラクタへの入力値を取得
     */
    public function getValue(string $name, ?string $alias = null): mixed
    {
        $key = $alias ?? $name;
        return $this->data[$key] ?? null;
    }

}
