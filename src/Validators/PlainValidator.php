<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use PhpValueObject\Enums\ValidatorMode;

/**
 * 通常のバリデーション処理より先に実行され、他のバリデーション処理をスキップするバリデータ
 *
 * @example
 * ```php
 * #[PlainValidator([ValidationClass::class, 'validate'])]
 * public string $value;
 * ```
 *
 * @phpstan-import-type validator_callable from Validatorable
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class PlainValidator extends FunctionValidator
{
    public function getMode(): ValidatorMode
    {
        return ValidatorMode::PLAIN;
    }

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
        $validator = $this->resolveValidator();
        return $validator($value);
    }
}
