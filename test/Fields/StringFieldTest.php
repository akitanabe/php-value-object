<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Fields\StringField;

class StringFieldValidateTestClass
{
    public string $prop;
}

class StringFieldTest extends TestCase
{
    /**
     * @return array<string, array{value: mixed, expectException: bool}>
     */
    public static function validateDataProvider(): array
    {
        return [
            '文字列の場合は検証が成功する' => [
                'value' => 'valid string',
                'expectException' => false,
            ],
            '空文字列でallowEmptyがtrueの場合は検証が成功する' => [
                'value' => '',
                'expectException' => false,
            ],
            '空文字列でallowEmptyがfalseの場合は例外が発生する' => [
                'value' => '',
                'expectException' => true,
                'allowEmpty' => false,
            ],
            '文字列以外の場合は例外が発生する' => [
                'value' => 123,
                'expectException' => true,
            ],
            '最小長より短い場合は例外が発生する' => [

                'value' => 'a',
                'expectException' => true,
                'minLength' => 2,
            ],
            '最大長より長い場合は例外が発生する' => [

                'value' => 'abcdef',
                'expectException' => true,
                'maxLength' => 5,
            ],
            'パターンに一致しない場合は例外が発生する' => [

                'value' => 'abc123',
                'expectException' => true,
                'pattern' => '/^[0-9]+$/',
            ],
            'パターンに一致する場合は検証が成功する' => [

                'value' => '12345',
                'expectException' => false,
                'pattern' => '/^[0-9]+$/',
            ],
        ];
    }

    /**
     * @param mixed $value
     * @param bool $expectException
     * @param bool $allowEmpty
     * @param positive-int $minLength
     * @param positive-int $maxLength
     * @param string $pattern
     */
    #[Test]
    #[DataProvider('validateDataProvider')]
    public function testValidate(
        mixed $value,
        bool $expectException,
        bool $allowEmpty = true,
        int $minLength = 1,
        int $maxLength = PHP_INT_MAX,
        string $pattern = '',
    ): void {

        $field = new StringField(
            allowEmpty: $allowEmpty,
            minLength: $minLength,
            maxLength: $maxLength,
            pattern: $pattern,
        );

        if ($expectException) {
            $this->expectException(ValidationException::class);
        }

        $field->validate($value);

        // @phpstan-ignore method.alreadyNarrowedType (例外が発生しなければテストは成功)
        $this->assertTrue(true);
    }
}
