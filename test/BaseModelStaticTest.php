<?php

declare(strict_types=1);

namespace PhSculptis\Test;

use PhSculptis\BaseModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use stdClass;

final class StaticTestModel extends BaseModel
{
    public string $name;

    public int $age;
}

class BaseModelStaticTest extends TestCase
{
    #[Test]
    public function arrayTest(): void
    {
        $staticTestModel = StaticTestModel::fromArray([
            'name' => 'John',
            'age' => 20,
        ],);

        $this->assertSame('John', $staticTestModel->name);
        $this->assertSame(20, $staticTestModel->age);
    }

    #[Test]
    public function objectTest(): void
    {
        $obj = new stdClass();

        $obj->name = 'Bob';
        $obj->age = 30;
        $staticTestObject = StaticTestModel::fromObject($obj);

        $this->assertSame('Bob', $staticTestObject->name);
        $this->assertSame(30, $staticTestObject->age);
    }
}
