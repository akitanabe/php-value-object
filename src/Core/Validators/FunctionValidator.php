<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use Closure;
use LogicException;
use PhpValueObject\Core\Definitions\FunctionValidatorDefinition;
use PhpValueObject\Core\ValidatorDefinitions;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\ValidatorMode;

/**
 * ユーザー入力バリデーション処理の基底クラス
 */
abstract class FunctionValidator implements Validatorable
{
    public function __construct(
        protected readonly Closure $validator,
    ) {}

    /**
     * バリデーション処理を実行する
     * 具象クラスで実装する
     */
    abstract public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed;

    public static function build(ValidatorDefinitions $definitions): Validatorable
    {
        $functionalValidatorQueue = $definitions->get(FunctionValidatorDefinition::class);

        if ($functionalValidatorQueue === null) {
            throw new LogicException('FunctionalValidatorQueueDefinition is not set.');
        }

        $functionalValidator = $functionalValidatorQueue->dequeue();

        /**
         * @var class-string<FunctionValidator> $funtionValidatorClass
         */
        $funtionValidatorClass = match ($functionalValidator->getMode()) {
            ValidatorMode::BEFORE => FunctionBeforeValidator::class,
            ValidatorMode::AFTER => FunctionAfterValidator::class,
            ValidatorMode::WRAP => FunctionWrapValidator::class,
            ValidatorMode::PLAIN => FunctionPlainValidator::class,
        };

        $validator = $functionalValidator->resolveValidator();

        return new $funtionValidatorClass($validator);
    }
}
