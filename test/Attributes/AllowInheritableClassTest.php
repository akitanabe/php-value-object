<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\AllowInheritableClass;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;

#[AllowInheritableClass]
class AllowInheritableClassValue extends BaseValueObject
{
}

class NotAllowInheritableValue extends BaseValueObject
{
}


class AllowInheritableClassTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowInheritableClass(): void
    {
        new AllowInheritableClassValue();
    }

    #[Test]
    public function notAllowInheritableClass(): void
    {
        $this->expectException(InheritableClassException::class);
        new NotAllowInheritableValue();
    }
}
