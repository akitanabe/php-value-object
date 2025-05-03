<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * ユーザー入力バリデーションを実行するクラス
 * 実行タイミングはシステムバリデータの実行後
 */
final class FunctionAfterValidator extends FunctionValidator
{
    /**
     * バリデーション処理を実行する
     * 次のハンドラーを実行後、その結果に対して自身のバリデーションを実行する
     *
     * @param mixed $value 検証する値
     * @param ValidatorFunctionWrapHandler|null $handler 内部バリデーション処理をラップするハンドラ
     * @return mixed バリデーション後の値
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // 次のハンドラーが存在する場合は先に実行
        if ($handler !== null) {
            $value = $handler($value);
        }

        // 次のハンドラーの結果に対してバリデーション処理を実行
        return ($this->validator)($value);
    }
}
