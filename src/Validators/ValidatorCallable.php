<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;

/**
 * バリデーション処理 (callable) とその実行モード (ValidatorMode) を提供するインターフェース
 * @phpstan-type validator_callable callable-string|class-string|array{string|object, string}|Closure
 */
interface ValidatorCallable
{
    /**
     * バリデーション処理を行う callable を解決して返す
     *
     */
    public function resolveValidator(): Closure;

    /**
     * バリデーションのモードを取得する
     *
     * @return ValidatorMode バリデーションモード
     */
    public function getMode(): ValidatorMode;
}
