<?php

declare(strict_types=1);

namespace PhpValueObject\Validation;

use Attribute;

/**
 * setPropertyValueの前にバリデーションを実行するAttribute
 * 
 * @example
 * ```php
 * #[AfterValidator([ValidationClass::class, 'formatString'])]
 * public string $value;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class AfterValidator extends Validator
{
}
