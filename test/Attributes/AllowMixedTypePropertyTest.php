<?php

declare(strict_types=1);

use PhpValueObject\Attributes\AllowMixedTypeProperty;
use PhpValueObject\BaseValueObject;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AllowMixedTypeProperty]
final class AllowMixedTypePropertyValue extends BaseValueObject
{
    public mixed $string = 'string';
}

final class StrictMixedPropertyTypeValue extends BaseValueObject
{
    public mixed $string = 'string';
}

class AllowMixedTypePropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowMixedTypeProperty(): void
    {
        AllowMixedTypePropertyValue::fromArray();
    }

    #[Test]
    public function execption(): void
    {
        $this->expectException(TypeError::class);
        StrictMixedPropertyTypeValue::fromArray();
    }
}
