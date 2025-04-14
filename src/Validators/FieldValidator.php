<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use Attribute;
use PhpValueObject\Enums\ValidatorMode;
use InvalidArgumentException;

/**
 * @phpstan-import-type validator_callable from Validatorable
 * @phpstan-type field_validator_mode 'plain'|'wrap'|'before'|'after'
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class FieldValidator implements Validatorable
{
    /**
     * @var validator_callable
     */
    private string|array|Closure $validator;

    private ValidatorMode $mode;

    /**
     * @param field_validator_mode $mode "plain", "wrap", "before" または "after" を指定
     * @throws InvalidArgumentException mode が不正な場合
     */
    public function __construct(
        public readonly string $field,
        string $mode = 'after',
    ) {

        $this->mode = match (strtolower($mode)) {
            'plain' => ValidatorMode::PLAIN,
            'wrap' => ValidatorMode::WRAP,
            'before' => ValidatorMode::BEFORE,
            // @phpstan-ignore match.alwaysTrue (afterはデフォルトだが入力値が不正な場合に例外を投げるため常にtrueではない)
            'after' => ValidatorMode::AFTER,
            default => throw new InvalidArgumentException(
                "Invalid validator mode: {$mode}. Expected \"plain\", \"wrap\", \"before\" or \"after\"",
            ),
        };
    }

    /**
     * @param validator_callable $validator バリデーション処理を行うcallable（静的メソッド）
     * @return void
     */
    public function setValidator(string|array|Closure $validator): void
    {
        $this->validator = $validator;
    }

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $validator = FieldsHelper::createFactory($this->validator);
        $args = ($handler !== null) ? [$value, $handler] : [$value];

        return $validator(...$args);
    }

    /**
     * @return ValidatorMode
     */
    public function getMode(): ValidatorMode
    {
        return $this->mode;
    }
}
