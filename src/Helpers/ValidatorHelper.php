<?php

declare(strict_types=1);

namespace PhpValueObject\Helpers;

use SplQueue;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * バリデータ関連のヘルパーメソッドを提供するクラス
 */
class ValidatorHelper
{
    /**
     * バリデータ配列からSplQueueを生成する
     *
     * @param array<Validatorable> $validators
     * @return SplQueue<Validatorable>
     */
    public static function createValidatorQueue(array $validators): SplQueue
    {
        $queue = new SplQueue();

        foreach ($validators as $validator) {
            $queue->enqueue($validator);
        }

        return $queue;
    }
}
