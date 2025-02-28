<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\BaseField;
use InvalidArgumentException;
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

class BaseFieldTest extends TestCase
{
    #[Test]
    public function factoryWithIdentityFunction(): void
    {
        $field = new class extends BaseField {};

        $result = $field->factory('test');

        $this->assertEquals('test', $result);
    }

    #[Test]
    public function factoryWithCustomFunction(): void
    {
        $customFactory = fn($value): string => strtoupper($value);
        $field = new class (factory: $customFactory) extends BaseField {};

        $result = $field->factory('test');

        $this->assertEquals($customFactory('test'), $result);
    }

    #[Test]
    public function factoryWithCallableString(): void
    {
        $field = new class (factory: 'strtolower') extends BaseField {};

        $result = $field->factory('TEST');

        $this->assertEquals(strtolower('test'), $result);
    }

    #[Test]
    public function factoryWithInvalidCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore argument.type
        $result = new class (factory: 'notCallable') extends BaseField {};
    }

    #[Test]
    public function factoryWithClass(): void
    {

        $field = new class (factory: DateTimeFactory::class) extends BaseField {};

        $result = $field->factory('2021-01-01');

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2021-01-01', $result->format('Y-m-d'));
    }

    #[Test]
    public function factoryWithCallableArray(): void
    {

        $field = new class (factory: [DateTimeFactory::class, 'create']) extends BaseField {};

        $result = $field->factory('2022-01-01');

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2022-01-01', $result->format('Y-m-d'));
    }


}
