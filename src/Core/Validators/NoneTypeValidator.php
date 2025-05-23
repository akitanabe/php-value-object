<?php

declare(strict_types=1);

namespace PhSculptis\Core\Validators;

use PhSculptis\Config\FieldConfig;
use PhSculptis\Config\ModelConfig;
use PhSculptis\Enums\TypeHintType;
use PhSculptis\Exceptions\InvalidPropertyStateException;
use PhSculptis\Support\PropertyMetadata;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;

/**
 * 型が指定されていないプロパティを検証するバリデータ
 */
class NoneTypeValidator implements Validatorable
{
    use ValidatorBuildTrait;

    public function __construct(
        private readonly ModelConfig $modelConfig,
        private readonly FieldConfig $fieldConfig,
        private readonly PropertyMetadata $metadata,
    ) {}

    /**
     * 型が指定されていないプロパティを検証
     *
     * @throws InvalidPropertyStateException
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $hasInvalidNoneType = array_any(
            $this->metadata->typeHints,
            fn($typeHint): bool =>
            (
                $typeHint->type === TypeHintType::NONE
                && ($this->modelConfig->noneTypeProperty->disallow() && $this->fieldConfig->noneTypeProperty->disallow())
            ),
        );

        if ($hasInvalidNoneType) {
            throw new InvalidPropertyStateException(
                "{$this->metadata->class}::\${$this->metadata->name} is invalid property state. not allow none property type.",
            );
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
