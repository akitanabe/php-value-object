<?php

declare(strict_types=1);

namespace PhpValueObject\Attributes;

use PhpValueObject\Options\Allowable;
use Attribute;

/**
 * プロパティにmixed型を許可する
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AllowMixedTypeProperty implements Allowable
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
