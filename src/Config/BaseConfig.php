<?php

declare(strict_types=1);

namespace PhpValueObject\Config;

class BaseConfig
{
    /**
     * @param array<string, bool> $initializeParams
     */
    final protected function initialize(array $initializeParams): void
    {
        foreach ($initializeParams as $propertyName => $allow) {
            $this->{$propertyName} = $allow ? new Allow() : new NotAllow();
        }
    }
}
