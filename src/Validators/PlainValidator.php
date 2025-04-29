<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;

/**
 * 通常のバリデーション処理より先に実行され、他のバリデーション処理をスキップするバリデータ
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
final class PlainValidator extends FunctionalValidator
{
    protected FunctionalValidatorMode $mode = FunctionalValidatorMode::PLAIN;
}
