<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\AllowMixedTypeProperty;


#[AllowMixedTypeProperty]
final class AllowMixedTypePropertyValue extends BaseValueObject
{
    public mixed $string = "string";
}


final class StrictMixedPropertyTypeValue extends BaseValueObject
{
    public mixed $string = "string";
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
