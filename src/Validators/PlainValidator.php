<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;

/**
 * システムバリデータの実行前にバリデーションを実行するAttribute
 * このバリデータが実行されたら次のバリデータは実行されない(前に登録されているバリデータは実行される)
 *
 * @example
 * ```php
 * #[PlainValidator([ValidationClass::class, 'validate'])]
 * public string $value;
 * ```
 *
 * @phpstan-import-type validator_callable from ValidatorCallable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class PlainValidator extends FunctionalValidator
{
    protected ValidatorMode $mode = ValidatorMode::PLAIN;
}
