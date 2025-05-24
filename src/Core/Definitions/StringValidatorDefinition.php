<?php

declare(strict_types=1);

namespace PhSculptis\Core\Definitions;

/**
 * 文字列バリデーションの定義を保持するクラス
 */
class StringValidatorDefinition
{
    /**
     * @param bool $allowEmpty 空文字を許可するかどうか
     * @param int $minLength 最小文字数
     * @param int $maxLength 最大文字数
     * @param string $pattern 正規表現パターン
     */
    public function __construct(
        public readonly bool $allowEmpty = true,
        public readonly int $minLength = 1,
        public readonly int $maxLength = PHP_INT_MAX,
        public readonly string $pattern = '',
    ) {}
}
