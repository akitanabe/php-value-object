<?php

declare(strict_types=1);

namespace PhpValueObject\Config;

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
