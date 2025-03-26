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
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class AfterValidator extends BaseValidator
{
    /**
     * @return 'after'
     */
    public function getMode(): string
    {
        return 'after';
    }
}
