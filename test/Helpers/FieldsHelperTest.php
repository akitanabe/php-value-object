<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Helpers;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Helpers\FieldsHelper;
use DateTime;

class DateTimeFactory
{
    public function __invoke(string $value): DateTime
    {
        return new DateTime($value);
    }

    public static function create(string $value): DateTime
    {
        return new DateTime($value);
    }

}


class FieldsHelperTest extends TestCase
{
    #[Test]
    public function identity(): void
    {
        $result = FieldsHelper::identity('test');

        $this->assertEquals('test', $result);
    }

    #[Test]
    public function createFactoryWithCustomFunction(): void
    {
        $customFactory = fn($value): string => strtoupper($value);
        $factoryFn = FieldsHelper::createFactory($customFactory);

        $result = $factoryFn('test');

        $this->assertEquals($customFactory('test'), $result);
    }

    #[Test]
    public function createFactoryWithCallableString(): void
    {
        $factoryFn = FieldsHelper::createFactory('strtolower');

        $result = $factoryFn('TEST');

        $this->assertEquals(strtolower('test'), $result);
    }

    #[Test]
    public function factoryWithInvalidCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore argument.type
        $factoryFn = FieldsHelper::createFactory('not_a_callable');
    }

    #[Test]
    public function factoryWithClass(): void
    {

        $factoryFn = FieldsHelper::createFactory(DateTimeFactory::class);

        $result = $factoryFn('2021-01-01');

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2021-01-01', $result->format('Y-m-d'));
    }

    #[Test]
    public function factoryWithCallableArray(): void
    {

        $factoryFn = FieldsHelper::createFactory([DateTimeFactory::class, 'create']);

        $result = $factoryFn('2022-01-01');

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2022-01-01', $result->format('Y-m-d'));
    }


}
