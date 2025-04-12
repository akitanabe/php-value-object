<?php

declare(strict_types=1);

namespace PhpValueObject\Support;

use ArrayIterator;
use PhpValueObject\Exceptions\ValidationException;
use InvalidArgumentException;
use PhpValueObject\Validators\Validatorable;

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
     */
    public function __invoke(mixed $value): mixed
    {
        if ($this->currentIndex < 0) {
            return $value;
        }

        $this->validators->seek($this->currentIndex);

        $validator = $this->validators->current();
        $this->validators->next();

        $nextHandler = new self($this->validators);

        return match ($validator->getMode()) {
            'before', 'after', 'field' => $nextHandler($validator->validate($value)),
            default => throw new InvalidArgumentException('Invalid validator mode'),
        };
    }
}
