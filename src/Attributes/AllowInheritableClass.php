<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Attributes;

use Attribute;
use Akitanabe\PhpValueObject\Options\Allowable;

/**
 * 継承可能クラスを許可する
 * (finalキーワードをつけていないクラスを許可する)
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AllowInheritableClass implements Allowable
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
