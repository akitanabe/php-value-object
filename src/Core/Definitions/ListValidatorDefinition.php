<?php

declare(strict_types=1);

namespace PhSculptis\Core\Definitions;

/**
 * リスト（配列）バリデーションの定義を保持するクラス
 */
class ListValidatorDefinition
{
    /**
     * @param ?string $type リストの要素の型名（"int", "float", "string", "object"など）またはクラス名
     */
    public function __construct(
        public readonly ?string $type = null,
    ) {}
}
