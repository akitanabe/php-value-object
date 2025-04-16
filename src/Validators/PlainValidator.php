<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use PhpValueObject\Enums\ValidatorMode;

/**
 * 通常のバリデーション処理より先に実行され、他のバリデーション処理をスキップするバリデータ
 *
 * @example
 * ```php
 * #[PlainValidator([ValidationClass::class, 'validate'])]
 * public string $value;
 * ```
 *
 * @phpstan-import-type validator_callable from Validatorable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class PlainValidator extends FunctionValidator
{
    public function getMode(): ValidatorMode
    {
        return ValidatorMode::PLAIN;
    }
}
