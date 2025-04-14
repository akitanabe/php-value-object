<?php

declare(strict_types=1);

namespace PhpValueObject\Enums;

/**
 * バリデーション処理の実行順序を示すEnum
 */
enum ValidatorMode: string
{
    /**
     * 他のバリデーション処理の前に実行し、他のバリデーション処理をスキップする
     */
    case PLAIN = 'plain';

    /**
     * 前後の値を比較するバリデーション処理
     */
    case WRAP = 'wrap';

    /**
     * 他のバリデーション処理の前に実行
     */
    case BEFORE = 'before';

    /**
     * 内部バリデーション処理として実行
     */
    case INTERNAL = 'internal';

    /**
     * 他のバリデーション処理の後に実行
     */
    case AFTER = 'after';

    /**
     * バリデーションの優先順位を取得
     *
     * @return int 優先順位（数値が小さいほど優先順位が高い）
     */
    public function getPriority(): int
    {
        return match ($this) {
            self::PLAIN => 0,
            self::WRAP => 1,
            self::BEFORE => 1,  // WRAPとBEFOREは同じ優先順位
            self::INTERNAL => 2,
            self::AFTER => 3,
        };
    }
}
