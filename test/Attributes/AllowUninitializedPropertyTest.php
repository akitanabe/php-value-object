<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\AllowUninitializedProperty;
use Akitanabe\PhpValueObject\Exceptions\BaseValueObjectException;

#[AllowUninitializedProperty]
final class AllowUninitiallizedValue extends BaseValueObject
{
    public string $string;
    public int $int;
}

final class DefaultPropertyValue extends BaseValueObject
{
    public string $string = "string";

    public function __construct(
        public int $int = 123
    ) {
        parent::__construct(...func_get_args());
    }
}

final class ExceptionValue extends BaseValueObject
{
    public string $string;
    public int $int;
}


class AllowUninitializedPropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowUninitializedProperty()
    {
        new AllowUninitiallizedValue();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function defaultPropertyIsInitialized()
    {
        new DefaultPropertyValue();
    }

    #[Test]
    public function execption()
    {
        $this->expectException(BaseValueObjectException::class);
        new ExceptionValue();
    }
}
