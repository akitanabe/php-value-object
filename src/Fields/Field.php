<?php

declare(strict_types=1);

namespace PhSculptis\Fields;

use Attribute;
use PhSculptis\Core\Definitions\NoneDefinition;
use PhSculptis\Core\Validators\IdenticalValidator;

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
