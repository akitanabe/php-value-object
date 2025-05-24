<?php

declare(strict_types=1);

namespace PhSculptis\Config;

final class Allow implements Allowable
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
