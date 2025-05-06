<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Core\Validators\IdenticalValidator;
use LogicException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Core\Validators\Validatorable;

final class ValidatorFunctionWrapHandler
{
    private Validatorable $validator;

    private ?self $nextHandler = null;

    private readonly ValidatorQueue $validatorQueue;

    /**
     * バリデータのビルドに必要な定義
     */
    private readonly ValidatorDefinitions $validatorDefinitions;

    /**
     * @param ValidatorQueue $validatorQueue バリデータクラス名のキュー
     * @param ValidatorDefinitions $validatorDefinitions バリデータ定義
     */
    public function __construct(
        ValidatorQueue $validatorQueue,
        ValidatorDefinitions $validatorDefinitions,
    ) {
        $this->validatorQueue = $validatorQueue;
        $this->validatorDefinitions = $validatorDefinitions;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     * @throws ValidationException
     * @throws LogicException
     */
    public function __invoke(mixed $value): mixed
    {
        // 実行時にバリデータとnextHandlerを取得する
        if (isset($this->validator) === false) {
            $this->lazyLoadValidator();
        }

        // バリデータに次のハンドラーを含めて実行を委譲
        // 各バリデータ内部で次のハンドラーを呼び出すかどうかを決定する
        return $this->validator->validate($value, $this->nextHandler);
    }

    /**
     * バリデータを遅延ロードする
     */
    private function lazyLoadValidator(): void
    {
        // バリデータキューが空になった場合はIdenticalValidatorを使用して返すのみにする
        if ($this->validatorQueue->isEmpty()) {
            $this->validator = new IdenticalValidator();
            return;
        }

        // バリデータクラス名を取得
        $validatorClass = $this->validatorQueue->dequeue();

        // Validatorableインターフェースのbuildメソッドを使用してインスタンス化
        /** @var Validatorable */
        $this->validator = $validatorClass::build($this->validatorDefinitions);

        // 次のハンドラーを作成
        $this->nextHandler = new self($this->validatorQueue, $this->validatorDefinitions);
    }
}
