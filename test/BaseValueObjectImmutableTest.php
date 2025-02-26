<?php

declare(strict_types=1);

use PhpValueObject\BaseValueObject;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class Immutable
{
}

final class ImmutableTestValue extends BaseValueObject
{
    public readonly Immutable $test;
}

class BaseValueObjectImmutableTest extends TestCase
{
    #[Test]
    public function immutableClone(): void
    {
        $test = new Immutable();
        $immutable = ImmutableTestValue::fromArray([
            'test' => $test,
        ], );

        $cloneImmutable = clone $immutable;

        $this->assertSame($test, $immutable->test);
        $this->assertNotSame($test, $cloneImmutable->test);
    }
}
