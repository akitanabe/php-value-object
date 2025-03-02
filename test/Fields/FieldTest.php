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
use UnexpectedValueException;

use function strtolower as _strtolower;

/**
 * @phpstan-import-type Defaults from \PhpValueObject\Test\Fields\FieldTest
 */
class DateTimeFactory
{
    /**
     * @param Defaults $data
     */
    public function __invoke(array $data): DateTime
    {
        return new DateTime($data['withClass']);
    }

    /**
     * @param Defaults $data
     */
    public static function create(array $data): DateTime
    {
        return new DateTime($data['withCallableArray']);
    }

    public static function now(): DateTime
    {
        return new DateTime();
    }

}

/**
 * @param array{test:string, callableString:string, withClass:string, withCallableArray:string, inAlias:string} $data
 * @return string
 */
function strtolower(array $data): string
{
    return _strtolower($data['callableString']);
}

function defaults(): string
{
    return 'default';
}


#[ConfigModel(allowUninitializedProperty: true)]
final class TestModel extends BaseModel
{
    #[Field]
    public readonly string $test;

    #[Field]
    public string $default = 'DEFAULT';

    #[Field(defaultFactory: __NAMESPACE__ . '\\strtolower')]
    public readonly string $callableString;

    #[Field(defaultFactory: DateTimeFactory::class)]
    public readonly DateTime $withClass;

    #[Field(defaultFactory: [DateTimeFactory::class, 'create'])]
    public readonly DateTime $withCallableArray;

    #[Field(defaultFactory: [DateTimeFactory::class, 'now'])]
    public readonly DateTime $byFacotry;

    #[Field(alias: 'in_alias')]
    public readonly string $inAlias;

}

#[ConfigModel(allowUninitializedProperty: true)]
final class InvalidCallableModel extends BaseModel
{
    // @phpstan-ignore argument.type
    #[Field(defaultFactory: 'notCallable')]
    public readonly string $callable;

}
final class DefaultBothModel extends BaseModel
{
    #[Field(defaultFactory: __NAMESPACE__ . '\\defaults')]
    public string $bothDefault = 'DEFAULT';
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
        $model = TestModel::fromArray([
            ...$defaults,
            'test' => 'test',
        ]);

        $this->assertSame('test', $model->test);
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryWithCallableString(array $defaults): void
    {

        $model = TestModel::fromArray([
            ...$defaults,
            'callableString' => 'CALLABLE',
        ]);

        $this->assertSame('callable', $model->callableString);

    }

    #[Test]
    public function assertFactoryWithInvalidCallable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $model = InvalidCallableModel::fromArray(['callable' => 'invalid']);
    }


    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryWithClass(array $defaults): void
    {
        $model = TestModel::fromArray([
            ...$defaults,
            'withClass' => '2021-01-01',
        ]);

        $this->assertInstanceOf(DateTime::class, $model->withClass);
        $this->assertEquals('2021-01-01', $model->withClass->format('Y-m-d'));
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryWithCallableArray(array $defaults): void
    {

        $model = TestModel::fromArray([
            ...$defaults,
            'withCallableArray' => '2021-01-01',
        ]);

        $this->assertInstanceOf(DateTime::class, $model->withCallableArray);
        $this->assertEquals('2021-01-01', $model->withCallableArray->format('Y-m-d'));
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function factoryByFactoryFn(array $defaults): void
    {
        $model = TestModel::fromArray($defaults);

        $now = new DateTime();

        $this->assertInstanceOf(DateTime::class, $model->byFacotry);
        $this->assertEquals($now->format('Y-m-d'), $model->byFacotry->format('Y-m-d'));
    }

    #[Test]
    public function assertDefaultBoth(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $model = DefaultBothModel::fromArray();
    }

    /**
     * @param Defaults $defaults
     */
    #[Test]
    #[DataProvider('defaultsProvider')]
    public function alias(array $defaults): void
    {
        $model = TestModel::fromArray(['in_alias' => 'in_alias', ...$defaults]);

        $this->assertEquals('in_alias', $model->inAlias);
    }


}
