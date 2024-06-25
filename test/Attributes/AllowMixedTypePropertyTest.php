<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\AllowMixedTypeProperty;


#[AllowMixedTypeProperty]
class AllowMixedTypePropertyValue extends BaseValueObject
{
    public mixed $string = "string";
}


class StrictMixedPropertyTypeValue extends BaseValueObject
{
    public mixed $string = "string";
}


class AllowMixedTypePropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowMixedTypeProperty()
    {
        new AllowMixedTypePropertyValue();
    }

    #[Test]
    public function execption()
    {
        $this->expectException(TypeError::class);
        new StrictMixedPropertyTypeValue();
    }
}
