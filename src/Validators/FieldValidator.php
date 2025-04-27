<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;
use Attribute;
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
     * @param validator_callable $validator バリデーション処理を行うcallable（静的メソッド）
     * @return void
     */
    public function setValidator(string|array|Closure $validator): void
    {
        $this->validator = $validator;
    }

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // モードに応じた適切なFunctionValidatorを生成
        // @phpstan-ignore match.unhandled (コンストラクタでmodeを検証済み)
        $functionValidator = match ($this->mode) {
            'plain' => new PlainValidator($this->validator),
            'wrap' => new WrapValidator($this->validator),
            'before' => new BeforeValidator($this->validator),
            'after' => new AfterValidator($this->validator),
        };

        // 生成したFunctionValidatorのvalidateメソッドを呼び出す
        return $functionValidator->validate($value, $handler);
    }
}
