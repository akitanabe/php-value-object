<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Options;

use Akitanabe\PhpValueObject\Options\Allowable;

final class NotAllow implements Allowable
{
    public function allow(): bool
    {
        return false;
    }

    public function disallow(): bool
    {
        return true;
    }
}
