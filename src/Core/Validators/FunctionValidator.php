<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use Closure;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * バリデーション処理の基底クラス
 *
 * @example
 * ```php
 * #[FunctionBeforeValidator([ValidationClass::class, 'validateLength'])]
 * #[FunctionAfterValidator([ValidationClass::class, 'formatString'])]
 * public string $value;
 * ```
 *
 */
abstract class FunctionValidator implements Validatorable
{
    public function __construct(
        protected readonly Closure $validator,
    ) {}

    /**
     * バリデーション処理を実行する
     * 具象クラスで実装する
     */
    abstract public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed;
}
