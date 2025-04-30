<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use Closure;
use PhpValueObject\Core\Validators\ListValidator;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * ListField
 * @phpstan-template T
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ListField extends BaseField
{
    /**
     * @param ?string $type リストの要素の型名（"int", "float", "string", "object"など）またはクラス名
     * @param ?default_factory $defaultFactory
     * @param ?string $alias
     */
    public function __construct(
        private readonly ?string $type = null,
        string|array|Closure|null $defaultFactory = null,
        ?string $alias = null,
    ) {
        parent::__construct($defaultFactory, $alias);
    }

    /**
     * ListValidatorを取得
     *
     * @return Validatorable
     */
    public function getValidator(): Validatorable
    {
        return new ListValidator($this->type);
    }
}
