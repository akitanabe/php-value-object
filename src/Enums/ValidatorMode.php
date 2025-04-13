<?php

declare(strict_types=1);

namespace PhpValueObject\Enums;

/**
 * バリデーション処理の実行順序を示すEnum
 */
enum ValidatorMode: int
{
    /**
     * 他のバリデーション処理の前に実行し、他のバリデーション処理をスキップする
     */
    case PLAIN = 0;

    /**
     * 他のバリデーション処理の前に実行
     */
    case BEFORE = 1;

    /**
     * フィールドのバリデーション処理として実行
     */
    case FIELD = 2;

    /**
     * 他のバリデーション処理の後に実行
     */
    case AFTER = 3;
}
