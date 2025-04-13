<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Enums\ValidatorMode;

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

    abstract public function getMode(): ValidatorMode;

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $validator = FieldsHelper::createFactory($this->validator);

        $args = ($handler !== null) ? [$value, $handler] : [$value];

        return $validator(...$args);
    }
}
