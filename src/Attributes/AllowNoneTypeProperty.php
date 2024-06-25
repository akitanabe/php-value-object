<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Attributes;

use Attribute;
use Akitanabe\PhpValueObject\Options\Allowable;

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
}
