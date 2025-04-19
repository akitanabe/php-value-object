<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Support\PropertyMetadata;

/**
 * プロパティの初期化状態を検証するValidator
 */
class PropertyInitializedValidator extends CorePropertyValidator
{
    private readonly ModelConfig $modelConfig;
    private readonly FieldConfig $fieldConfig;

    public function __construct(
        ModelConfig $modelConfig,
        FieldConfig $fieldConfig,
        PropertyMetadata $metadata,
        ValidatorMode $mode = ValidatorMode::INTERNAL,
    ) {
        parent::__construct($metadata, $mode);
        $this->modelConfig = $modelConfig;
        $this->fieldConfig = $fieldConfig;
    }

    /**
     * 値の初期化状態を検証する
     *
     * @throws InvalidPropertyStateException プロパティが未初期化で、それが許可されていない場合
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // プロパティが未初期化の場合
        if ($this->metadata->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            // 未初期化プロパティが許可されている場合はそのまま返す
            if ($this->modelConfig->uninitializedProperty->allow() || $this->fieldConfig->uninitializedProperty->allow()) {
                return $value;
            }

            throw new InvalidPropertyStateException(
                "{$this->metadata->class}::\${$this->metadata->name} is not initialized. not allow uninitialized property.",
            );
        }

        return $value;
    }
}
