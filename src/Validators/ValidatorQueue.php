<?php

namespace PhpValueObject\Validators;

use SplQueue;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * Validator名をキューとして管理するクラス
 *
 * @extends SplQueue<class-string<Validatorable>>
 */
class ValidatorQueue extends SplQueue
{
    /**
     * @param class-string<Validatorable>[] $validators
     */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validator) {
            $this->enqueue($validator);
        }
    }
}
