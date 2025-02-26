<?php

declare(strict_types=1);

namespace PhpValueObject\Attributes;

use PhpValueObject\Options\Allowable;
use Attribute;

/**
 * プロパティが型が指定されていないことを許可する
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AllowNoneTypeProperty implements Allowable
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
