<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use PhpValueObject\Validators\FunctionalValidatorMode;

/**
 * setPropertyValueの前にバリデーションを実行するAttribute
 *
 * @example
 * ```php
 * #[AfterValidator([ValidationClass::class, 'formatString'])]
 * public string $value;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class AfterValidator extends FunctionalValidator
{
    protected FunctionalValidatorMode $mode = FunctionalValidatorMode::AFTER;
}
