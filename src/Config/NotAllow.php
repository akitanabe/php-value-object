<?php

declare(strict_types=1);

namespace PhpValueObject\Config;

use PhpValueObject\Config\Allowable;

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
