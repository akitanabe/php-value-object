<?php

declare(strict_types=1);

namespace PhpValueObject\Attributes;

use PhpValueObject\Options\Allowable;
use Attribute;

/**
 * 初期化していないプロパティを許可する
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AllowUninitializedProperty implements Allowable
{
    public function allow(): bool
    {
        return true;
    }

    public function disallow(): bool
    {
        return false;
    }
}
