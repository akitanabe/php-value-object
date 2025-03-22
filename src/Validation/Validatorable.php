<?php

declare(strict_types=1);

namespace PhpValueObject\Validation;

/**
 * バリデーション処理のインターフェース
 */
interface Validatorable
{
    /**
     * バリデーション処理を実行する
     *
     * @param mixed $value 検証する値
     * @return mixed バリデーション後の値
     * @throws \PhpValueObject\Exceptions\ValidationException バリデーションに失敗した場合
     */
    public function validate(mixed $value): mixed;
}
