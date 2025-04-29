<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use Closure;
use InvalidArgumentException;
use PhpValueObject\Core\Validators\FunctionValidator;
use PhpValueObject\Core\Validators\PlainFunctionValidator;
use PhpValueObject\Core\Validators\AfterFunctionValidator;
use PhpValueObject\Core\Validators\BeforeFunctionValidator;
use PhpValueObject\Core\Validators\WrapFunctionValidator;

/**
 * @phpstan-import-type validator_callable from Validatorable
 * @phpstan-type field_validator_mode 'plain'|'wrap'|'before'|'after'
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class FieldValidator
{
    /**
     * @var field_validator_mode
     */
    private readonly string $mode;

    /**
     * @param field_validator_mode $mode "plain", "wrap", "before" または "after" を指定
     * @throws InvalidArgumentException mode が不正な場合
     */
    public function __construct(
        public readonly string $field,
        string $mode = 'after',
    ) {
        $normalizedMode = strtolower($mode);
        if (in_array($normalizedMode, ['plain', 'wrap', 'before', 'after'], true) === false) {
            throw new InvalidArgumentException(
                "Invalid validator mode: {$mode}. Expected \"plain\", \"wrap\", \"before\" or \"after\"",
            );
        }
        $this->mode = $normalizedMode;
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
            'plain' => new PlainFunctionValidator($validator),
            'wrap' => new WrapFunctionValidator($validator),
            'before' => new BeforeFunctionValidator($validator),
            'after' => new AfterFunctionValidator($validator),
        };
    }
}
