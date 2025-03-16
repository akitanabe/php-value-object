<?php

declare(strict_types=1);

namespace PhpValueObject\Test;

use PhpValueObject\BaseModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class Immutable {}

final class ImmutableTestModel extends BaseModel
{
    // @phpstan-ignore property.uninitializedReadonly
    public readonly Immutable $test;
}

class BaseModelImmutableTest extends TestCase
{
    #[Test]
    public function immutableClone(): void
    {
        $test = new Immutable();
        $immutable = ImmutableTestModel::fromArray([
            'test' => $test,
        ],);

        $cloneImmutable = clone $immutable;

        $this->assertSame($test, $immutable->test);
        $this->assertNotSame($test, $cloneImmutable->test);
    }
}
