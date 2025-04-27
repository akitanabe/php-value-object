<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use LogicException;

/**
 * 値をラップしてバリデーションを実行するAttribute
 *
 * @example
 * ```php
 * #[WrapValidator(fn($value) => strtoupper($value))]
 * public string $value;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class WrapValidator extends FunctionValidator
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
            throw new LogicException('WrapValidator must be executed with a handler.');
        }

        // バリデータ関数を解決し、バリデータに次のハンドラーを渡す
        $validator = $this->resolveValidator();
        return $validator($value, $handler);
    }
}
