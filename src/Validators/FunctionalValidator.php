<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use InvalidArgumentException;

/**
 * 関数ベースのバリデーション処理の基底クラス
 * ValidatorCallable を実装し、バリデーション関数とそのモードを提供する
 *
 * @phpstan-import-type validator_callable from ValidatorCallable
 */
abstract class FunctionalValidator implements ValidatorCallable
{
    protected FunctionalValidatorMode $mode;

    /**
     * @param validator_callable $validator
     */
    public function __construct(
        protected readonly string|array|Closure $validator,
    ) {}

    /**
     * バリデーション処理を行う callable を返す
     *
     * @throws InvalidArgumentException
     */
    final public function resolveValidator(): Closure
    {
        return FieldsHelper::createFactory($this->validator);
    }

    /**
     * バリデーションのモードを取得する
     *
     * @return FunctionalValidatorMode バリデーションモード
     */
    final public function getMode(): FunctionalValidatorMode
    {
        return $this->mode;
    }
}
