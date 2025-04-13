<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use ArrayIterator;
use LogicException;
use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Exceptions\ValidationException;

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
        // currentIndexが-1の場合は、バリデーションを行わない。
        // $this->validators->valid() === falseと同じ意味
        if ($this->currentIndex < 0) {
            return $value;
        }

        $this->validators->seek($this->currentIndex);
        $validator = $this->validators->current();
        $validatorMode = $validator->getMode();

        // PLAINモードのバリデーターは常に先頭（currentIndex = 0）でなければならない
        if ($validatorMode === ValidatorMode::PLAIN && $this->currentIndex !== 0) {
            throw new LogicException('ValidatorMode::PLAIN must be at the first position (currentIndex = 0)');
        }

        // 次のハンドラーを準備
        $this->validators->next();
        $nextHandler = new self($this->validators);

        // モードに応じて処理を分岐
        return match ($validatorMode) {
            // PLAINは常に先頭なので自身のバリデーション以外を実行しない
            ValidatorMode::PLAIN => $validator->validate($value),
            // WRAPはvalidator内で次のハンドラを実行するか委ねる
            ValidatorMode::WRAP => $validator->validate($value, $nextHandler),
            // BEFORE,AFTERは次のハンドラを実行する
            default => $nextHandler($validator->validate($value)),
        };
    }
}
