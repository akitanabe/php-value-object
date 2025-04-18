<?php

declare(strict_types=1);

namespace PhpValueObject\Config;

interface Allowable
{
    public function allow(): bool;

    public function disallow(): bool;
}
