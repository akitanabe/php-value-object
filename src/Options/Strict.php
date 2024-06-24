<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Options;

use ReflectionClass;
use Akitanabe\PhpValueObject\Options\Allowable;
use Akitanabe\PhpValueObject\Options\NotAllow;
use Akitanabe\PhpValueObject\Attributes\AllowUninitializedProperty;
use Akitanabe\PhpValueObject\Helpers\AttributeHelper;

final class Strict
{
    /**
     * @var AllowUninitializedProperty|NotAllow
     * 初期化していないプロパティを許可する
     */
    public Allowable $uninitializedProperty;

    public function __construct(ReflectionClass $refClass)
    {
        $this->uninitializedProperty = AttributeHelper::getAttribute(
            $refClass,
            AllowUninitializedProperty::class
        )?->newInstance() ?? new NotAllow();
    }
}
