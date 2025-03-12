<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use PhpValueObject\Support\PropertyOperator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Field extends BaseField
{
    public function validate(PropertyOperator $propertyOperator): void {}
}
