<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Field extends BaseField
{
    public function validate(mixed $value): void {}
}
