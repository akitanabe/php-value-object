<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use ArrayIterator;
use LogicException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Core\Validators\Validatorable;

final class ValidatorFunctionWrapHandler
{
    private readonly ?Validatorable $validator;
    private readonly ArrayIterator $validators;

    /**
     * @param ArrayIterator<int,Validatorable> $validators
     */
    public function __construct(
        ArrayIterator $validators,
    ) {
        $this->validators = $validators;
        $this->validator = $this->validators->current();

        // 次のハンドラーを準備
        $this->validators->next();
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
