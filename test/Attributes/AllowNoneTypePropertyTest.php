<?php

declare(strict_types=1);

use PhpValueObject\Attributes\AllowNoneTypeProperty;
use PhpValueObject\BaseValueObject;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[AllowNoneTypeProperty]
final class AllowNoneTypePropertyValue extends BaseValueObject
{
    // @phpstan-ignore missingType.property
    public $string = 'string';
}

final class StrictNonePropertyTypeValue extends BaseValueObject
{
    // @phpstan-ignore missingType.property
    public $string = 'string';
}

class AllowNoneTypePropertyTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowNoneTypeProperty(): void
    {
        AllowNoneTypePropertyValue::fromArray();
    }

    #[Test]
    public function execption(): void
    {
        $this->expectException(TypeError::class);
        StrictNonePropertyTypeValue::fromArray();
    }
}
