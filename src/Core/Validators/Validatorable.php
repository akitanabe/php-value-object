<?php

declare(strict_types=1);

namespace PhSculptis\Core\Validators;

use PhSculptis\Core\ValidatorDefinitions;
use PhSculptis\Exceptions\ValidationException;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;
use LogicException;

/**
 * バリデーション処理のインターフェース
 *
 * @phpstan-type validator_callable callable-string|class-string|array{string|object, string}|\Closure
 */
interface Validatorable
{
    /**
     * バリデーション処理を実行する
     *
     * @param mixed $value 検証する値
     * @param ValidatorFunctionWrapHandler|null $handler 内部バリデーション処理をラップするハンドラ
     * @return mixed バリデーション後の値
     * @throws ValidationException バリデーションに失敗した場合
     * @throws LogicException FunctionWrapValidatorがハンドラなしで実行された場合
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed;

    /**
     * バリデータを構築する
     *
     * @param ValidatorDefinitions $definitions バリデータ定義
     * @return Validatorable 構築されたバリデータインスタンス
     */
    public static function build(ValidatorDefinitions $definitions): Validatorable;
}
