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
    private readonly SplQueue $validators;

    /**
     * @param validator_queue $validators
     */
    public function __construct(
        SplQueue $validators,
    ) {
        $this->validators = $validators;
        $this->validator = $this->validators->isEmpty() === false ? $this->validators->dequeue() : null;
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

        $nextHandler = new self($this->validators);

        // バリデータに次のハンドラーを含めて実行を委譲
        // 各バリデータ内部で次のハンドラーを呼び出すかどうかを決定する
        return $this->validator->validate($value, $nextHandler);
    }
}
