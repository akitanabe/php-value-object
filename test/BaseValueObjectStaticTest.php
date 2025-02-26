<?php

declare(strict_types=1);

use PhpValueObject\BaseValueObject;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StaticTestObject extends BaseValueObject
{
    public string $name;

    public int $age;
}

class BaseValueObjectStaticTest extends TestCase
{
    #[Test]
    public function arrayTest(): void
    {
        $staticTestObject = StaticTestObject::fromArray([
            'name' => 'John',
            'age' => 20,
        ], );

        $this->assertSame('John', $staticTestObject->name);
        $this->assertSame(20, $staticTestObject->age);
    }

    #[Test]
    public function objectTest(): void
    {
        $obj = new stdClass();

        $obj->name = 'Bob';
        $obj->age = 30;
        $staticTestObject = StaticTestObject::fromObject($obj);

        $this->assertSame('Bob', $staticTestObject->name);
        $this->assertSame(30, $staticTestObject->age);
    }
}
