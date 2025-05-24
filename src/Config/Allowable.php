<?php

declare(strict_types=1);

namespace PhSculptis\Config;

interface Allowable
{
    public function allow(): bool;

    public function disallow(): bool;
}
