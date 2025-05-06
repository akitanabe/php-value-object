<?php

namespace PhpValueObject\Core\Definitions;

use SplQueue;
use PhpValueObject\Validators\ValidatorCallable;

/**
 * FunctionalValidatorをキューとして管理するクラス
 *
 * @extends SplQueue<ValidatorCallable>
 */
class FunctionValidatorDefinition extends SplQueue
{
    /**
     * @param ValidatorCallable[] $validators
     */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validator) {
            $this->enqueue($validator);
        }
    }
}
