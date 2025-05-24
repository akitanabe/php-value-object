<?php

declare(strict_types=1);

namespace PhSculptis\Core\Validators;

use PhSculptis\Validators\ValidatorFunctionWrapHandler;

/**
 * ユーザー入力バリデーションを実行するクラス
 * 実行タイミングはシステムバリデータの実行前
 *
 */
final class FunctionBeforeValidator extends FunctionValidator
{
    /**
     * バリデーション処理を実行する
     * 自身のバリデーションを実行後、次のハンドラーを呼び出す
     *
     * @param mixed $value 検証する値
     * @param ValidatorFunctionWrapHandler|null $handler 内部バリデーション処理をラップするハンドラ
     * @return mixed バリデーション後の値
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // バリデーション処理を実行
        $validatedValue = ($this->validator)($value);

        // 次のハンドラーが存在する場合は実行
        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
