<?php

namespace PhSculptis\Core\Definitions;

use SplQueue;
use PhSculptis\Validators\ValidatorCallable;

/**
 * ValidaotorCallableをimplementsしているクラスをキューとして管理
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
