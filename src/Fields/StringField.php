<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use Override;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Support\ValidatorFunctionWrapHandler;

/**
 * StringField
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class StringField extends BaseField
{
    /**
     *
     * @param ?default_factory $defaultFactory
     * @param ?string $alias
     * @param bool $allowEmpty
     * @param positive-int $minLength
     * @param positive-int $maxLength
     * @param string $pattern
     */
    public function __construct(
        string|array|Closure|null $defaultFactory = null,
        string|null $alias = null,
        private bool $allowEmpty = true,
        private int $minLength = 1,
        private int $maxLength = PHP_INT_MAX,
        private string $pattern = '',
    ) {
        parent::__construct($defaultFactory, $alias);
    }

    /**
     * 文字列のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
    #[Override]
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $invalidMessage = 'Invalid Field Value';
        if (is_string($value) === false) {
            throw new ValidationException("{$invalidMessage}. Must be string");
        }

        if ($this->allowEmpty === false && $value === '') {
            throw new ValidationException("{$invalidMessage}. Field Value cannot be empty");
        }

        if ($value === '') {
            return $value;
        }

        $valueLength = mb_strlen($value);

        if ($valueLength <= $this->minLength) {
            throw new ValidationException(
                "{$invalidMessage}. Too short. Must be at least {$this->minLength}characters",
            );
        }

        if ($valueLength >= $this->maxLength) {
            throw new ValidationException("{$invalidMessage}. Too long. Must be at most {$this->maxLength}characters");
        }

        if ($this->pattern !== '' && preg_match($this->pattern, $value) === 0) {
            throw new ValidationException("{$invalidMessage}. Invalid format");
        }

        return $value;
    }
}
