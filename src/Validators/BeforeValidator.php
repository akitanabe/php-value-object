<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;

/**
 * システムバリデータの実行前にバリデーションを実行するAttribute
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
    protected ValidatorMode $mode = ValidatorMode::BEFORE;
}
