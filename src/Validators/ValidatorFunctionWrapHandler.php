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
    private readonly Validatorable $validator;

    private readonly ?self $nextHandler;

    /**
     * @param validator_queue $validatorQueue
     */
    public function __construct(
        SplQueue $validatorQueue,
    ) {
        if ($validatorQueue->isEmpty() == false) {
            $this->validator = $validatorQueue->dequeue();
            $this->nextHandler = new self($validatorQueue);

        } else {
            // バリデータキューが空になった場合はIdenticalValidatorを使用して返すのみにする
            $this->validator = new IdenticalValidator();
            $this->nextHandler = null;
        }

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
        // バリデータに次のハンドラーを含めて実行を委譲
        // 各バリデータ内部で次のハンドラーを呼び出すかどうかを決定する
        return $this->validator->validate($value, $this->nextHandler);
    }
}
