<?php

declare(strict_types=1);

use Akitanabe\PhpValueObject\Attributes\AllowUninitializedProperty;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AllowUninitializedProperty]
final class AllowUninitiallizedPropertyValue extends BaseValueObject
{
    public string $string;

    public int $int;
}

final class AllowInitialziedPropertyValue extends BaseValueObject
{
    public string $string = 'string';

    public int $int = 123;
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
        AllowUninitiallizedPropertyValue::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowInitialziedPropertyValue(): void
    {
        AllowInitialziedPropertyValue::fromArray();
    }

    #[Test]
    public function execption(): void
    {
        $this->expectException(UninitializedException::class);
        NotAllowInitializedPropertyValue::fromArray();
    }
}
