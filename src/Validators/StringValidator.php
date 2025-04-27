<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Exceptions\ValidationException;

/**
 * 文字列のバリデーターを実装するクラス
 */
class StringValidator implements Validatorable
{
    /**
     * @param bool $allowEmpty 空文字を許可するかどうか
     * @param int $minLength 最小文字数
     * @param int $maxLength 最大文字数
     * @param string $pattern 正規表現パターン
     */
    public function __construct(
        private bool $allowEmpty = true,
        private int $minLength = 1,
        private int $maxLength = PHP_INT_MAX,
        private string $pattern = '',
    ) {}

    /**
     * 文字列のバリデーションを実行
     *
     * @param mixed $value バリデーション対象の値
     * @param ValidatorFunctionWrapHandler|null $handler バリデーションハンドラー
     * @return string バリデーション後の値
     * @throws ValidationException バリデーションエラーが発生した場合
     */
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

        if ($valueLength < $this->minLength) {
            throw new ValidationException(
                "{$invalidMessage}. Too short. Must be at least {$this->minLength} characters",
            );
        }

        if ($valueLength > $this->maxLength) {
            throw new ValidationException("{$invalidMessage}. Too long. Must be at most {$this->maxLength} characters");
        }

        if ($this->pattern !== '' && preg_match($this->pattern, $value) === 0) {
            throw new ValidationException("{$invalidMessage}. Invalid format");
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }

}
