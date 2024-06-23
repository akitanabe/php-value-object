<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Attributes;

use Attribute;
use Akitanabe\PhpValueObject\Options\Allowable;

#[Attribute(Attribute::TARGET_CLASS)]
final class AllowUninitializedProperty implements Allowable
{
    public function allow(): bool
    {
        return true;
    }
}