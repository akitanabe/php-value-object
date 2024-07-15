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
    // @phpstan-ignore missingType.property
    public $string = "string";
}


final class StrictNonePropertyTypeValue extends BaseValueObject
{
    // @phpstan-ignore missingType.property
    public $string = "string";
}


class AllowNoneTypePropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowNoneTypeProperty(): void
    {
        new AllowNoneTypePropertyValue();
    }

    #[Test]
    public function execption(): void
    {
        $this->expectException(TypeError::class);
        new StrictNonePropertyTypeValue();
    }
}
