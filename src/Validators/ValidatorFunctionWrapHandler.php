<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Core\Validators\IdenticalValidator;
use SplQueue;
use LogicException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * @phpstan-type validator_queue SplQueue<Validatorable>
 */
final class ValidatorFunctionWrapHandler
{
    private ?Validatorable $validator = null;
    private ?self $nextHandler = null;
    private readonly SplQueue $validatorQueue;
    /**
     * @param validator_queue $validatorQueue
     */
    public function __construct(
        SplQueue $validatorQueue,
    ) {
        $this->validatorQueue = $validatorQueue;

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
        $this->lazyLoadValidator();

        // バリデータに次のハンドラーを含めて実行を委譲
        // 各バリデータ内部で次のハンドラーを呼び出すかどうかを決定する
        return $this->validator->validate($value, $this->nextHandler);
    }

    /**
     * バリデータを遅延ロードする
     */
    private function lazyLoadValidator(): void
    {
        // すでにバリデータが設定されている場合は何もしない
        if ($this->validator !== null) {
            return;
        }

        // バリデータキューが空になった場合はIdenticalValidatorを使用して返すのみにする
        if ($this->validatorQueue->isEmpty()) {
            $this->validator = new IdenticalValidator();
            return;
        }

        $this->validator = $this->validatorQueue->dequeue();
        $this->nextHandler = new self($this->validatorQueue);
    }
}
