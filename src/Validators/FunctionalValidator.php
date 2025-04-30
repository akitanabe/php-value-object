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
    protected ValidatorMode $mode;
    /**
     * バリデーション処理を行う callable
     *
     * @var validator_callable
     */
    private readonly string|array|Closure $callable;

    /**
     * @param validator_callable $validator
     */
    public function __construct(string|array|Closure $validator)
    {
        $this->callable = $validator;
    }

    /**
     * バリデーション処理を行う callable を返す
     *
     * @throws InvalidArgumentException
     */
    final public function resolveValidator(): Closure
    {
        return FieldsHelper::createFactory($this->callable);
    }

    /**
     * バリデーションのモードを取得する
     *
     * @return ValidatorMode バリデーションモード
     */
    final public function getMode(): ValidatorMode
    {
        return $this->mode;
    }
}
