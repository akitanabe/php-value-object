<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Support\PropertyMetadata;

/**
 * プロパティの型定義を検証するバリデータ
 */
class PropertyTypeValidator extends CorePropertyValidator
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
     * プロパティの型定義を検証
     *
     * @throws InvalidPropertyStateException
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        foreach ($this->metadata->typeHints as $typeHint) {
            if (
                (
                    // 型が指定されていない場合
                    $typeHint->type === TypeHintType::NONE
                    && ($this->modelConfig->noneTypeProperty->disallow() && $this->fieldConfig->noneTypeProperty->disallow())
                )
                || (
                    // mixed型の場合
                    $typeHint->type === TypeHintType::MIXED
                    && ($this->modelConfig->mixedTypeProperty->disallow() && $this->fieldConfig->mixedTypeProperty->disallow())
                )
            ) {
                throw new InvalidPropertyStateException(
                    "{$this->metadata->class}::\${$this->metadata->name} is invalid property state. not allow {$typeHint->type->value} property type.",
                );
            }
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
