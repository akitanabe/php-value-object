<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use PhpValueObject\Validators\FunctionalValidatorMode;

/**
 * PropertyOperator::valueへの代入前にバリデーションを実行するAttribute
 *
 * @example
 * ```php
 * #[BeforeValidator([ValidationClass::class, 'validateLength'])]
 * public string $value;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class BeforeValidator extends FunctionalValidator
{
    protected FunctionalValidatorMode $mode = FunctionalValidatorMode::BEFORE;
}
