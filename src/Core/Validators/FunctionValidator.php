<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;

/**
 * バリデーション処理の基底クラス
 *
 * @example
 * ```php
 * #[BeforeFunctionValidator([ValidationClass::class, 'validateLength'])]
 * #[AfterFunctionValidator([ValidationClass::class, 'formatString'])]
 * public string $value;
 * ```
 *
 * @phpstan-import-type validator_callable from Validatorable
 */
abstract class FunctionValidator implements Validatorable
{
    /**
     * @param validator_callable $validator バリデーション処理を行うcallable
     */
    public function __construct(
        protected readonly string|array|Closure $validator,
    ) {}

    /**
     * バリデーション処理を実行する
     * 具象クラスで実装する
     */
    abstract public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed;

    /**
     * バリデータのcallableを解決して返す
     *
     * @return callable バリデーション処理を行うcallable
     */
    protected function resolveValidator(): callable
    {
        return FieldsHelper::createFactory($this->validator);
    }
}
