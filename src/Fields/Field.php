<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use PhpValueObject\Fields\BaseField;
use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Field extends BaseField {}
