<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

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
    /**
     * @return 'after'
     */
    public function getMode(): string
    {
        return 'after';
    }
}
