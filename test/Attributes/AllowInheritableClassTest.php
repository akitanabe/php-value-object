<?php

declare(strict_types=1);

use Akitanabe\PhpValueObject\Attributes\AllowInheritableClass;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AllowInheritableClass]
class AllowInheritableClassValue extends BaseValueObject {}

class NotAllowInheritableValue extends BaseValueObject {}

class AllowInheritableClassTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowInheritableClass(): void
    {
        AllowInheritableClassValue::fromArray();
    }

    #[Test]
    public function notAllowInheritableClass(): void
    {
        $this->expectException(InheritableClassException::class);
        NotAllowInheritableValue::fromArray();
    }
}
