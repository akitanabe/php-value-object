<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;

/**
 * システムバリデータの実行前にバリデーションを実行するAttribute
 * 次のバリデータを実行するかを入力バリデータに委任する
 *
 * @example
 * ```php
 * #[WrapValidator(fn($value) => strtoupper($value))]
 * public string $value;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class WrapValidator extends FunctionalValidator
{
    protected ValidatorMode $mode = ValidatorMode::WRAP;
}
