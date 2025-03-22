<?php

declare(strict_types=1);

namespace PhpValueObject\Validation;

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
 * @phpstan-type validator_callable callable-string|class-string|array{string|object, string}|Closure
 */
abstract class Validator implements Validatorable
{
    /**
     * @param validator_callable $validator バリデーション処理を行うcallable
     */
    public function __construct(
        private readonly string|array|Closure $validator
    ) {
    }

    public function validate(mixed $value): mixed
    {
        $validator = FieldsHelper::createFactory($this->validator);
        return $validator($value);
    }
}
