<?php

declare(strict_types=1);

namespace PhpValueObject\Attributes;

use PhpValueObject\Options\Allowable;
use Attribute;

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
