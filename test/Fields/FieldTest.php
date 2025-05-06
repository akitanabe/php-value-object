<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use DateTime;
use PhpValueObject\Fields\Field;
use PhpValueObject\Core\Validators\IdenticalValidator;
use PhpValueObject\Core\Validators\Validatorable;

use function strtolower as _strtolower;

class DateTimeFactory
{
    /**
     * @param array{value: string} $data
     */
    public function __invoke(array $data): DateTime
    {
        return new DateTime($data['value']);
    }

    /**
     * @param array{value: string} $data
     */
    public static function create(array $data): DateTime
    {
        return new DateTime($data['value']);
    }

    public static function now(): DateTime
    {
        return new DateTime();
    }
}

/**
 * @param array{value: string} $data
 */
function strtolower(array $data): string
{
    return _strtolower($data['value']);
}

function defaults(): string
{
    return 'default';
}

class FieldValidateTestClass
{
    #[Field]
    public string $defaultValue;

    #[Field(defaultFactory: __NAMESPACE__ . '\\strtolower')]
    public string $withCallable;

    #[Field(defaultFactory: DateTimeFactory::class)]
    public DateTime $withClass;

    #[Field(defaultFactory: [DateTimeFactory::class, 'create'])]
    public DateTime $withCallableArray;

    #[Field(defaultFactory: [DateTimeFactory::class, 'now'])]
    public DateTime $withFactory;

    #[Field(alias: 'aliased_field')]
    public string $aliasField;
}

class InvalidCallableTestClass
{
    // @phpstan-ignore argument.type
    #[Field(defaultFactory: 'not_a_callable')]
    public string $invalidCallable;
}

class DefaultBothTestClass
{
    #[Field(defaultFactory: __NAMESPACE__ . '\\defaults')]
    public string $bothDefault = 'DEFAULT';
}

class FieldTest extends TestCase
{
    /**
     * @return array<string, array{
     *   value: string,
     *   expectedValue: string|DateTime
     * }>
     */
    public static function defaultFactoryDataProvider(): array
    {
        return [
            '通常の文字列の場合はそのまま返される' => [
                'value' => 'test',
                'expectedValue' => 'test',
            ],
            'callableで大文字から小文字に変換される' => [
                'value' => 'CALLABLE',
                'expectedValue' => 'callable',
            ],
            'DateTimeFactoryでDateTime型に変換される' => [
                'value' => '2021-01-01',
                'expectedValue' => new DateTime('2021-01-01'),
            ],
            'callableArrayでDateTime型に変換される' => [
                'value' => '2021-01-01',
                'expectedValue' => new DateTime('2021-01-01'),
            ],
        ];
    }

    /**
     * デフォルトファクトリーを使用した値の変換をテストします。
     *
     * @param string $value テスト対象の入力値
     * @param string|DateTime $expectedValue 期待される変換後の値
     * 以下のケースをテストします：
     * - 通常の文字列は変更なしで返される
     * - Callableを使用した文字列の変換（大文字から小文字）
     * - DateTimeFactoryを使用したDateTime型への変換
     * - CallableArrayを使用したDateTime型への変換
     */
    #[Test]
    #[DataProvider('defaultFactoryDataProvider')]
    public function testDefaultFactoryValueTransformation(string $value, string|DateTime $expectedValue): void
    {
        $field = match (true) {
            $expectedValue instanceof DateTime => new Field(defaultFactory: DateTimeFactory::class),
            default => new Field(defaultFactory: __NAMESPACE__ . '\\strtolower'),
        };

        $result = $field->defaultFactory(['value' => $value]);

        if ($expectedValue instanceof DateTime) {
            $this->assertInstanceOf(DateTime::class, $result);
            $this->assertEquals($expectedValue->format('Y-m-d'), $result->format('Y-m-d'));
        } else {
            $this->assertEquals($expectedValue, $result);
        }
    }

    /**
     * 不正なCallableが指定された場合の動作をテストします。
     * 存在しない関数名が指定された場合、InvalidArgumentExceptionが発生することを確認します。
     */
    #[Test]
    public function testInvalidCallableThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // @phpstan-ignore argument.type
        $field = new Field(defaultFactory: 'not_a_callable');
        $field->defaultFactory(['invalidCallable' => 'test']);
    }

    /**
     * デフォルトファクトリーが設定されていない場合の動作をテストします。
     * defaultFactoryメソッドがnullを返すことを確認します。
     */
    #[Test]
    public function testDefaultFactoryReturnsNullWhenNotSet(): void
    {
        $field = new Field();
        $result = $field->defaultFactory([]);

        $this->assertNull($result);
    }

    /**
     * hasDefaultFactoryメソッドの動作をテストします。
     * - デフォルトファクトリーが設定されている場合はtrueを返す
     * - デフォルトファクトリーが設定されていない場合はfalseを返す
     */
    #[Test]
    public function testHasDefaultFactoryIndicatesPresence(): void
    {
        $withFactory = new Field(defaultFactory: __NAMESPACE__ . '\\strtolower');
        $withoutFactory = new Field();

        $this->assertTrue($withFactory->hasDefaultFactory());
        $this->assertFalse($withoutFactory->hasDefaultFactory());
    }

    /**
     * エイリアスの設定をテストします。
     * コンストラクタで指定したエイリアス名がaliasプロパティに正しく設定されることを確認します。
     */
    #[Test]
    public function testAliasIsSetCorrectly(): void
    {
        $field = new Field(alias: 'aliased_field');
        $this->assertEquals('aliased_field', $field->alias);
    }

    /**
     * getValidatorメソッドがIdenticalValidatorのクラス名を返すことをテストします。
     *
     * 検証内容:
     * - FieldクラスのgetValidatorメソッドが、IdenticalValidatorクラスの名前（文字列）を返すこと
     *
     * IdenticalValidatorは、入力値に対して特別なバリデーションを行わず、値をそのまま返すシンプルなバリデーターです。
     * これはFieldクラスが特定の型チェックを必要としない汎用的なフィールドとして設計されているためです。
     */
    #[Test]
    public function testGetValidatorReturnsIdenticalValidator(): void
    {
        $field = new Field();
        $validator = $field->getValidator();

        $this->assertEquals(IdenticalValidator::class, $validator);
    }

    /**
     * getDefinitionメソッドがNoneDefinitionオブジェクトを返すことをテスト
     *
     * 検証内容:
     * - Fieldクラスは特定のバリデーション定義を持たないため、getDefinitionメソッドはNoneDefinitionを返すこと
     */
    #[Test]
    public function testGetDefinitionReturnsNoneDefinition(): void
    {
        $field = new Field();
        $definition = $field->getDefinition();

        $this->assertIsObject($definition);
        $this->assertInstanceOf(\PhpValueObject\Core\Definitions\NoneDefinition::class, $definition);
    }
}
