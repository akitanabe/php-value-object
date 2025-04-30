<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

/**
 * バリデーションの実行タイミングや方法を示す Enum
 */
enum ValidatorMode
{
    /**
     * FunctionBeforeValidator を利用する場合
     */
    case BEFORE;

    /**
     * FunctionAfterValidator を利用する場合
     */
    case AFTER;

    /**
     * FunctionWrapValidator を利用する場合
     */
    case WRAP;

    /**
     * FunctionPlainValidator を利用する場合
     */
    case PLAIN;
}
