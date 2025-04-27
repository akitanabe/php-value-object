<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;

/**
 * 入力値をそのまま返すバリデーターを実装するクラス
 * バリデーションなしで値をそのまま返します
 */
class IdenticalValidator implements Validatorable
{
    /**
     * 値をそのまま返す
     *
     * @param mixed $value バリデーション対象の値
     * @param ValidatorFunctionWrapHandler|null $handler バリデーションハンドラー
     * @return mixed 入力値をそのまま返す
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
