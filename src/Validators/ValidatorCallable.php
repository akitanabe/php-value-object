<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;

/**
 * バリデーション処理 (callable) とその実行モード (FunctionalValidatorMode) を提供するインターフェース
 * @phpstan-type validator_callable callable-string|class-string|array{string|object, string}|Closure
 */
interface ValidatorCallable
{
    /**
     * バリデーション処理を行う callable を返す
     *
     * @return validator_callable
     */
    public function getCallable(): string|array|Closure;

    /**
     * バリデーションのモードを取得する
     *
     * @return FunctionalValidatorMode バリデーションモード
     */
    public function getMode(): FunctionalValidatorMode;
}
