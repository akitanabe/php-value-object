<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use PhpValueObject\Core\Validators\IdenticalValidator;
use PhpValueObject\Core\Validators\Validatorable;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Field extends BaseField
{
    public function getValidator(): string
    {
        return IdenticalValidator::class;
    }
}
