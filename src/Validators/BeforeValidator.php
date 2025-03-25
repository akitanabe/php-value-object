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
#[Attribute(Attribute::TARGET_PROPERTY)]
final class BeforeValidator extends Validator
{
    /**
     * @return 'before'
     */
    public function getMode(): string
    {
        return 'before';
    }
}
