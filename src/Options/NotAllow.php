<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Options;

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
