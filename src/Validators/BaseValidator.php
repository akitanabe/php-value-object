<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;

/**
 * バリデーション処理の基底クラス
 *
 * @example
 * ```php
 * #[BeforeValidator([ValidationClass::class, 'validateLength'])]
 * #[AfterValidator([ValidationClass::class, 'formatString'])]
 * public string $value;
 * ```
 *
 * @phpstan-import-type validator_callable from Validatorable
 */
abstract class BaseValidator implements Validatorable
{
    /**
     * @param validator_callable $validator バリデーション処理を行うcallable
     */
    public function __construct(
        private readonly string|array|Closure $validator,
    ) {}

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $validator = FieldsHelper::createFactory($this->validator);
        return $validator($value);
    }
}
