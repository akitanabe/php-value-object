<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;

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
final class BeforeValidator extends BaseValidator
{
    /**
     * @return 'before'
     */
    public function getMode(): string
    {
        return 'before';
    }
}
