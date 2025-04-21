<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Attribute;
use PhpValueObject\Validators\IdenticalValidator;
use PhpValueObject\Validators\Validatorable;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Field extends BaseField
{
    /**
     * IdenticalValidatorを取得
     * 値をそのまま返すバリデーター
     *
     * @return Validatorable
     */
    public function getValidator(): Validatorable
    {
        return new IdenticalValidator();
    }
}
