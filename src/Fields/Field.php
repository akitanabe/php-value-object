<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use PhpValueObject\Core\Definitions\NoneDefinition;
use PhpValueObject\Core\Validators\IdenticalValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Field extends BaseField
{
    public function getValidator(): string
    {
        return IdenticalValidator::class;
    }

    public function getDefinition(): object
    {
        return new NoneDefinition();
    }
}
