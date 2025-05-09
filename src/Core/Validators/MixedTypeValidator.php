<?php

declare(strict_types=1);

namespace PhpValueObject\Core\Validators;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * mixed型プロパティを検証するバリデータ
 */
class MixedTypeValidator implements Validatorable
{
    use ValidatorBuildTrait;

    public function __construct(
        private readonly ModelConfig $modelConfig,
        private readonly FieldConfig $fieldConfig,
        private readonly PropertyMetadata $metadata,
    ) {}

    /**
     * mixed型プロパティを検証
     *
     * @throws InvalidPropertyStateException
     */
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $hasInvalidMixedType = array_any(
            $this->metadata->typeHints,
            fn($typeHint): bool =>
            (
                $typeHint->type === TypeHintType::MIXED
                && ($this->modelConfig->mixedTypeProperty->disallow() && $this->fieldConfig->mixedTypeProperty->disallow())
            ),
        );

        if ($hasInvalidMixedType) {
            throw new InvalidPropertyStateException(
                "{$this->metadata->class}::\${$this->metadata->name} is invalid property state. not allow mixed property type.",
            );
        }

        $validatedValue = $value;

        if ($handler !== null) {
            return $handler($validatedValue);
        }

        return $validatedValue;
    }
}
