<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use ArrayIterator;
use LogicException;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Core\Validators\Validatorable;

final class ValidatorFunctionWrapHandler
{
    private readonly int $currentIndex;

    /**
     * @param ArrayIterator<int,Validatorable> $validators
     */
    public function __construct(
        private ArrayIterator $validators,
    ) {
        $this->currentIndex = $validators->key() > -1 ? $validators->key() : -1;
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
        if ($this->currentIndex < 0) {
            return $value;
        }

        // 現在のバリデータを取得
        $this->validators->seek($this->currentIndex);
        $validator = $this->validators->current();

        // 次のハンドラーを準備
        $this->validators->next();
        $nextHandler = new self($this->validators);

        // バリデータに次のハンドラーを含めて実行を委譲
        // 各バリデータ内部で次のハンドラーを呼び出すかどうかを決定する
        return $validator->validate($value, $nextHandler);
    }
}
