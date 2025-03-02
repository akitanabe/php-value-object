<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use DateTime;
use PhpValueObject\BaseModel;
use PhpValueObject\Fields\Field;
use PhpValueObject\Config\ConfigModel;

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

    public static function now(): DateTime
    {
        return new DateTime();
    }

}


#[ConfigModel(allowUninitializedProperty: true)]
final class TestValue extends BaseModel
{
    #[Field]
    public readonly string $test;

    #[Field(factory: 'strtolower')]
    public readonly string $callableString;

    #[Field(factory: DateTimeFactory::class)]
    public readonly DateTime $withClass;

    #[Field(factory: [DateTimeFactory::class, 'create'])]
    public readonly DateTime $withCallableArray;

    #[Field(factory: [DateTimeFactory::class, 'now'])]
    public readonly DateTime $byFacotry;

    #[Field(factory: 'strtoupper')]
    public string $default = 'default';

    #[Field(alias: 'in_alias')]
    public readonly string $inAlias;

}

#[ConfigModel(allowUninitializedProperty: true)]
final class InvalidCallableValue extends BaseModel
{
    // @phpstan-ignore argument.type
    #[Field(factory: 'notCallable')]
    public readonly string $callable;

}


/**
 * @phpstan-type Defaults array{test:string, callableString:string, withClass:string, withCallableArray:string, inAlias:string}
 */
class FieldTest extends TestCase
{
    /**
     * @return list<Defaults[]>
     */
    public static function defaultsProvider(): array
    {
        return [
            [
                [
                    'test' => '',
                    'callableString' => '',
                    'withClass' => '1970-01-01',
                    'withCallableArray' => '1970-01-01',
                    'inAlias' => 'inAlias',
                ],
            ],
        ];
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryWithIdentityFunction(array $defaults): void
    {
        $value = TestValue::fromArray([
            ...$defaults,
            'test' => 'test',
        ]);

        $this->assertSame('test', $value->test);
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryWithCallableString(array $defaults): void
    {

        $value = TestValue::fromArray([
            ...$defaults,
            'callableString' => 'CALLABLE',
        ]);

        $this->assertSame('callable', $value->callableString);

    }

    #[Test]
    public function factoryWithInvalidCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $value = InvalidCallableValue::fromArray(['callable' => 'invalid']);
    }


    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryWithClass(array $defaults): void
    {
        $value = TestValue::fromArray([
            ...$defaults,
            'withClass' => '2021-01-01',
        ]);

        $this->assertInstanceOf(DateTime::class, $value->withClass);
        $this->assertEquals('2021-01-01', $value->withClass->format('Y-m-d'));
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryWithCallableArray(array $defaults): void
    {

        $value = TestValue::fromArray([
            ...$defaults,
            'withCallableArray' => '2021-01-01',
        ]);

        $this->assertInstanceOf(DateTime::class, $value->withCallableArray);
        $this->assertEquals('2021-01-01', $value->withCallableArray->format('Y-m-d'));
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryByFactoryFn(array $defaults): void
    {
        $value = TestValue::fromArray($defaults);

        $now = new DateTime();

        $this->assertInstanceOf(DateTime::class, $value->byFacotry);
        $this->assertEquals($now->format('Y-m-d'), $value->byFacotry->format('Y-m-d'));
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryByPropertyDefault(array $defaults): void
    {
        $value = TestValue::fromArray($defaults);

        $this->assertEquals('default', $value->default);
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function alias(array $defaults): void
    {
        $value = TestValue::fromArray(['in_alias' => 'in_alias', ...$defaults]);

        $this->assertEquals('in_alias', $value->inAlias);
    }


}
