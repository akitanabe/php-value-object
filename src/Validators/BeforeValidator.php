<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;

/**
 * PropertyOperator::valueへの代入前にバリデーションを実行するAttribute
 *
 * @example
 * ```php
 * #[BeforeValidator([ValidationClass::class, 'validateLength'])]
 * public string $value;
 * ```
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class BeforeValidator extends FunctionValidator
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
        $validator = $this->resolveValidator();
        $validatedValue = $validator($value);

        // 次のハンドラーが存在する場合は実行
        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
