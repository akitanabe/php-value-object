<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use Closure;
use PhpValueObject\Core\Validators\FunctionValidator;
use PhpValueObject\Core\Validators\FunctionPlainValidator;
use PhpValueObject\Core\Validators\FunctionAfterValidator;
use PhpValueObject\Core\Validators\FunctionBeforeValidator;
use PhpValueObject\Core\Validators\FunctionWrapValidator;

/**
 * @phpstan-import-type validator_callable from ValidatorCallable
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class FieldValidator
{
    /**
     * バリデーターモード
     */
    private readonly FunctionalValidatorMode $mode;

    /**
     * @param string $field バリデーション対象のフィールド名
     */
    public function __construct(
        public readonly string $field,
        FunctionalValidatorMode $mode = FunctionalValidatorMode::AFTER,
    ) {
        $this->mode = $mode;
    }

    /**
     * 自身の mode と指定された callable から FunctionValidator インスタンスを生成する
     *
     * @param validator_callable $validator バリデーション処理を行う callable
     * @return FunctionValidator 生成された FunctionValidator インスタンス
     */
    public function getValidator(string|array|Closure $validator): FunctionValidator
    {
        return match ($this->mode) {
            FunctionalValidatorMode::PLAIN => new FunctionPlainValidator($validator),
            FunctionalValidatorMode::WRAP => new FunctionWrapValidator($validator),
            FunctionalValidatorMode::BEFORE => new FunctionBeforeValidator($validator),
            FunctionalValidatorMode::AFTER => new FunctionAfterValidator($validator),
        };
    }
}
