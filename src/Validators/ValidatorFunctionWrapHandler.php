<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use SplQueue;
use LogicException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * @phpstan-type validator_queue SplQueue<Validatorable>
 */
final class ValidatorFunctionWrapHandler
{
    private readonly ?Validatorable $validator;

    /**
     * @var validator_queue
     */
    private readonly SplQueue $validatorQueue;

    /**
     * @param validator_queue $validatorQueue
     */
    public function __construct(
        SplQueue $validatorQueue,
    ) {
        $this->validatorQueue = $validatorQueue;
        $this->validator = $this->validatorQueue->isEmpty() === false ? $this->validatorQueue->dequeue() : null;
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
        // バリデータがない場合は、値をそのまま返す
        if ($this->validator === null) {
            return $value;
        }

        $nextHandler = new self($this->validatorQueue);

        // バリデータに次のハンドラーを含めて実行を委譲
        // 各バリデータ内部で次のハンドラーを呼び出すかどうかを決定する
        return $this->validator->validate($value, $nextHandler);
    }
}
