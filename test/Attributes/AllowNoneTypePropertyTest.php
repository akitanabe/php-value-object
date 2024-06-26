<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Akitanabe\PhpValueObject\BaseValueObject;
use Akitanabe\PhpValueObject\Attributes\AllowNoneTypeProperty;


#[AllowNoneTypeProperty]
final class AllowNoneTypePropertyValue extends BaseValueObject
{
    public $string = "string";
}


final class StrictNonePropertyTypeValue extends BaseValueObject
{
    public $string = "string";
}


class AllowNoneTypePropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowNoneTypeProperty()
    {
        new AllowNoneTypePropertyValue();
    }

    #[Test]
    public function execption()
    {
        $this->expectException(TypeError::class);
        new StrictNonePropertyTypeValue();
    }
}
