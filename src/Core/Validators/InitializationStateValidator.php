<?php

declare(strict_types=1);

namespace PhSculptis\Core\Validators;

use PhSculptis\Config\FieldConfig;
use PhSculptis\Config\ModelConfig;
use PhSculptis\Enums\PropertyInitializedStatus;
use PhSculptis\Exceptions\InvalidPropertyStateException;
use PhSculptis\Support\PropertyMetadata;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;

/**
 * プロパティの初期化状態を検証するValidator
 */
class InitializationStateValidator implements Validatorable
{
    use ValidatorBuildTrait;

    public function __construct(
        private readonly ModelConfig $modelConfig,
        private readonly FieldConfig $fieldConfig,
        private readonly PropertyMetadata $metadata,
    ) {}

    /**
     * 値の初期化状態を検証する
     *
     * @throws InvalidPropertyStateException プロパティが未初期化で、それが許可されていない場合
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        // プロパティが未初期化の場合
        if ($this->metadata->initializedStatus === PropertyInitializedStatus::UNINITIALIZED) {
            // 未初期化プロパティが許可されている場合はそのまま返す（後続のハンドラーは実行しない）
            if ($this->modelConfig->uninitializedProperty->allow() || $this->fieldConfig->uninitializedProperty->allow()) {
                return $value;
            }

            throw new InvalidPropertyStateException(
                "{$this->metadata->class}::\${$this->metadata->name} is not initialized. not allow uninitialized property.",
            );
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
