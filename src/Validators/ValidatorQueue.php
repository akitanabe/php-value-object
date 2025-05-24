<?php

namespace PhSculptis\Validators;

use SplQueue;
use PhSculptis\Core\Validators\Validatorable;

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
