<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Exceptions\ValidationException;

/**
 * バリデーション処理のインターフェース
 *
 * @phpstan-type validator_mode 'before'|'after'
 * @phpstan-type validator_callable callable-string|class-string|array{string|object, string}|\Closure
 */
interface Validatorable
{
    /**
     * バリデーション処理を実行する
     *
     * @param mixed $value 検証する値
     * @return mixed バリデーション後の値
     * @throws ValidationException バリデーションに失敗した場合
     */
    public function validate(mixed $value): mixed;

    /**
     * バリデーション処理の実行順序を取得する
     * @return validator_mode バリデーション処理の実行順序
     */
    public function getMode(): string;

}
