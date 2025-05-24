<?php

declare(strict_types=1);

namespace PhSculptis\Validators;

use Closure;
use PhSculptis\Helpers\FieldsHelper;
use InvalidArgumentException;

/**
 * Attributeバリデーション設定の基底クラス
 * modeの指定は各具象クラスで行う
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
