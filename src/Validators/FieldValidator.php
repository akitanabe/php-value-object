<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use Attribute;
use PhpValueObject\Enums\ValidatorMode;

/**
 * @phpstan-import-type validator_callable from Validatorable
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class FieldValidator implements Validatorable
{
    /**
     * @var validator_callable
     */
    private string|array|Closure $validator;

    /**
     * @param ValidatorMode $mode
     */
    public function __construct(
        public readonly string $field,
        private ValidatorMode $mode = ValidatorMode::AFTER,
    ) {}

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
