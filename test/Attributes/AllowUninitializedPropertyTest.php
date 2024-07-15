<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\AllowUninitializedProperty;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;

#[AllowUninitializedProperty]
final class AllowUninitiallizedPropertyValue extends BaseValueObject
{
    public string $string;
    public int $int;
}

final class AllowInitialziedPropertyValue extends BaseValueObject
{
    public string $string = "string";

    public function __construct(
        public int $int = 123
    ) {
        parent::__construct(...func_get_args());
    }
}

final class NotAllowInitializedPropertyValue extends BaseValueObject
{
    public string $string;
    public int $int;
}


class AllowUninitializedPropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowUninitializedProperty(): void
    {
        new AllowUninitiallizedPropertyValue();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowInitialziedPropertyValue(): void
    {
        new AllowInitialziedPropertyValue();
    }

    #[Test]
    public function execption(): void
    {
        $this->expectException(UninitializedException::class);
        new NotAllowInitializedPropertyValue();
    }
}
