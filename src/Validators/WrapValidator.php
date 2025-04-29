<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;

/**
 * 値をラップしてバリデーションを実行するAttribute
 *
 * @example
 * ```php
 * #[WrapValidator(fn($value) => strtoupper($value))]
 * public string $value;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class WrapValidator extends FunctionalValidator
{
    protected FunctionalValidatorMode $mode = FunctionalValidatorMode::WRAP;
}
