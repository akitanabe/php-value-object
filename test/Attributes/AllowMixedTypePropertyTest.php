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
    public mixed $string;
}


class StrictPropertyTypeValue extends BaseValueObject
{
    public mixed $string;
}


class AllowMixedTypePropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowMixedTypeProperty()
    {
        new AllowMixedTypePropertyValue(string: "string");
    }

    #[Test]
    public function execption()
    {
        $this->expectException(TypeError::class);
        new StrictPropertyTypeValue(string: "string");
    }
}
