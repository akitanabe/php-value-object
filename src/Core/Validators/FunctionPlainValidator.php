<?php

declare(strict_types=1);

namespace PhSculptis\Core\Validators;

use PhSculptis\Validators\ValidatorFunctionWrapHandler;

/**
 * ユーザー入力バリデーションを実行するクラス
 * 実行タイミングはシステムバリデータの実行前で、次のハンドラーを呼び出さない
 * つまり、バリデーション処理を実行するが、このハンドラーの後に続くバリデーションは実行しない
 * (登録されているハンドラーは実行される。例えばFunctionAfterValidatorが先に登録されていればそれは実行される)
 */
final class FunctionPlainValidator extends FunctionValidator
{
    /**
     * バリデーション処理を実行する
     * 自身のバリデーションのみを実行し、次のハンドラーは呼び出さない
     *
     * @param mixed $value 検証する値
     * @param ValidatorFunctionWrapHandler|null $handler 内部バリデーション処理をラップするハンドラ（使用しない）
     * @return mixed バリデーション後の値
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // バリデーション処理を実行（次のハンドラーは呼び出さない）
        return ($this->validator)($value);
    }
}
