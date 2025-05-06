<?php

namespace PhpValueObject\Core\Definitions;

use SplQueue;
use PhpValueObject\Validators\FunctionalValidator;

/**
 * FunctionalValidatorをキューとして管理するクラス
 *
 * @extends SplQueue<FunctionalValidator>
 */
class FunctionValidatorDefinition extends SplQueue
{
    /**
     * @param FunctionalValidator[] $validators
     */
    public function __construct(array $validators = [])
    {
        foreach ($validators as $validator) {
            $this->enqueue($validator);
        }
    }
}
