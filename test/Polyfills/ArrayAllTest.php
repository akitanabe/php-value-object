<?php

declare(strict_types=1);

namespace Polyfills\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use TypeError;

class ArrayAllTest extends TestCase
{
    #[Test]
    public function testArrayAllWithEmptyArray(): void
    {
        $array = [];
        $callback = fn($value) => $value > 0;

        $result = array_all($array, $callback);

        $this->assertTrue($result);
    }

    #[Test]
    public function testArrayAllWithAllMatchingElements(): void
    {
        $array = [1, 2, 3];
        $callback = fn($value) => $value > 0;

        $result = array_all($array, $callback);

        $this->assertTrue($result);
    }

    #[Test]
    public function testArrayAllWithOneNonMatchingElement(): void
    {
        $array = [1, 2, 3];
        $callback = fn($value) => $value > 2;

        $result = array_all($array, $callback);

        $this->assertFalse($result);
    }

    #[Test]
    public function testArrayAllWithNoMatchingElements(): void
    {
        $array = [1, 2, 3];
        $callback = fn($value) => $value > 3;

        $result = array_all($array, $callback);

        $this->assertFalse($result);
    }

    #[Test]
    public function testArrayAllWithNonCallableCallback(): void
    {
        $this->expectException(TypeError::class);

        $array = [1, 2, 3];
        $callback = 'not_a_callable';

        // @phpstan-ignore argument.type
        array_all($array, $callback);
    }
}
