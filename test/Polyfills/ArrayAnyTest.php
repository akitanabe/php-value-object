<?php

declare(strict_types=1);

namespace Polyfills\Test;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use TypeError;

class ArrayAnyTest extends TestCase
{
    #[Test]
    public function testArrayAnyWithEmptyArray(): void
    {
        $array = [];
        $callback = fn($value) => $value > 0;

        $result = array_any($array, $callback);

        $this->assertFalse($result);
    }

    #[Test]
    public function testArrayAnyWithMatchingElement(): void
    {
        $array = [1, 2, 3];
        $callback = fn($value) => $value > 2;

        $result = array_any($array, $callback);

        $this->assertTrue($result);
    }

    #[Test]
    public function testArrayAnyWithNoMatchingElement(): void
    {
        $array = [1, 2, 3];
        $callback = fn($value) => $value > 3;

        $result = array_any($array, $callback);

        $this->assertFalse($result);
    }

    #[Test]
    public function testArrayAnyWithAllMatchingElements(): void
    {
        $array = [1, 2, 3];
        $callback = fn($value) => $value > 0;

        $result = array_any($array, $callback);

        $this->assertTrue($result);
    }

    #[Test]
    public function testArrayAnyWithNonCallableCallback(): void
    {
        $this->expectException(TypeError::class);

        $array = [1, 2, 3];
        $callback = 'not_a_callable';

        // @phpstan-ignore argument.type
        array_any($array, $callback);
    }
}
