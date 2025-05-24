<?php

declare(strict_types=1);

namespace PhSculptis\Fields;

use Attribute;
use Closure;
use PhSculptis\Core\Definitions\ListValidatorDefinition;
use PhSculptis\Core\Validators\ListValidator;

/**
 * ListField
 * @phpstan-import-type default_factory from BaseField
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ListField extends BaseField
{
    /**
     * バリデーション定義
     */
    private ListValidatorDefinition $definition;

    /**
     *
     * @param ?default_factory $defaultFactory
     * @param ?string $alias
     * @param ?string $type リストの要素の型名（"int", "float", "string", "object"など）またはクラス名
     */
    public function __construct(
        string|array|Closure|null $defaultFactory = null,
        string|null $alias = null,
        string|null $type = null,
    ) {
        parent::__construct($defaultFactory, $alias);
        $this->definition = new ListValidatorDefinition($type);
    }

    public function getValidator(): string
    {
        return ListValidator::class;
    }

    public function getDefinition(): object
    {
        return $this->definition;
    }
}
