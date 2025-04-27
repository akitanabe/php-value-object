<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Support\PropertyMetadata;

/**
 * プロパティバリデーションの基本機能を提供する内部コアバリデータ
 */
abstract class CorePropertyValidator implements Validatorable
{
    protected readonly ValidatorMode $mode;
    protected readonly PropertyMetadata $metadata;

    public function __construct(PropertyMetadata $metadata, ValidatorMode $mode = ValidatorMode::INTERNAL)
    {
        $this->metadata = $metadata;
        $this->mode = $mode;
    }

    /**
     * プロパティメタデータを取得する
     */
    public function getMetadata(): PropertyMetadata
    {
        return $this->metadata;
    }
}
