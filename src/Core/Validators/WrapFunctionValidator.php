<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use LogicException;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * 値をラップしてバリデーションを実行するクラス
 *
 * @example
 * ```php
 * #[WrapFunctionValidator(fn($value) => strtoupper($value))]
 * public string $value;
 * ```
 */
final class WrapFunctionValidator extends FunctionValidator
{
    /**
     * バリデーション処理を実行する
     * バリデータ関数に次のハンドラーを渡して実行する
     *
     * @param mixed $value 検証する値
     * @param ValidatorFunctionWrapHandler|null $handler 後続の処理を制御するハンドラー
     * @return mixed
     * @throws LogicException handlerがnullの場合（プログラムのロジックエラー）
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        if ($handler === null) {
            throw new LogicException('WrapFunctionValidator must be executed with a handler.');
        }

        // バリデータ関数を解決し、バリデータに次のハンドラーを渡す
        $validator = $this->resolveValidator();
        return $validator($value, $handler);
    }
}
