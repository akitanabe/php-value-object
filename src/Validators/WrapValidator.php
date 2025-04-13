<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use LogicException;
use PhpValueObject\Enums\ValidatorMode;

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
final class WrapValidator extends BaseValidator
{
    /**
     * @return ValidatorMode
     */
    public function getMode(): ValidatorMode
    {
        return ValidatorMode::WRAP;
    }

    /**
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

        return parent::validate($value, $handler);
    }
}
