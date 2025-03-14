<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PhpValueObject\Fields\DecimalField;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\InputData;
use PhpValueObject\Exceptions\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class DecimalFieldValidateTestClass
{
    public float $prop;
}

/**
 * DecimalFieldのバリデーション機能をテストするクラス
 *
 * 小数値の検証に関する以下の機能をテスト:
 * - 基本的な小数値の変換と検証
 * - 最大桁数のバリデーション
 * - 小数点以下の桁数のバリデーション
 * - 不正な入力値の検証
 * - 複数の制約を組み合わせた検証
 */
class DecimalFieldTest extends TestCase
{
    /**
     * テスト用のPropertyOperatorインスタンスを作成
     *
     * @param mixed $value バリデーション対象の値
     * @param DecimalField $field 検証に使用するDecimalFieldインスタンス
     * @return PropertyOperator 設定された検証用のPropertyOperator
     *
     * テストクラスのpropプロパティに対して:
     * 1. リフレクションを使用してプロパティ情報を取得
     * 2. 指定された値でInputDataを作成
     * 3. PropertyOperatorインスタンスを生成して返却
     */
    private function createPropertyOperator(mixed $value, DecimalField $field): PropertyOperator
    {
        $refProperty = new ReflectionProperty(DecimalFieldValidateTestClass::class, 'prop');
        $inputData = new InputData(['prop' => $value]);
        return new PropertyOperator($refProperty, $inputData, $field);
    }

    /**
     * 基本的な小数値の検証をテスト
     *
     * 文字列で与えられた小数値'123.45'が:
     * 1. バリデーションを通過すること
     * 2. 正しくfloat型の123.45に変換されること
     */
    #[Test]
    public function basicValidation(): void
    {
        $field = new DecimalField();
        $operator = $this->createPropertyOperator('123.45', $field);

        $field->validate($operator);
        $this->assertEquals(123.45, $operator->value);
    }

    /**
     * 最大桁数のバリデーションをテスト
     *
     * maxDigits=5の場合:
     * 1. 5桁以下の数値'123.45'は許可される
     * 2. 6桁の数値'1234.56'はValidationExceptionを発生
     *
     * 桁数は整数部と小数部の合計で計算
     */
    #[Test]
    public function maxDigitsValidation(): void
    {
        $field = new DecimalField(maxDigits: 5);
        $operator = $this->createPropertyOperator('123.45', $field);
        $field->validate($operator);
        $this->assertEquals(123.45, $operator->value);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Number must have no more than 5 digits in total');
        $field->validate($this->createPropertyOperator('1234.56', $field));
    }

    /**
     * 小数点以下の桁数制限のバリデーションをテスト
     *
     * decimalPlaces=2の場合:
     * 1. 小数点以下2桁の'123.45'は許可される
     * 2. 小数点以下3桁の'123.456'はValidationExceptionを発生
     */
    #[Test]
    public function decimalPlacesValidation(): void
    {
        $field = new DecimalField(decimalPlaces: 2);
        $operator = $this->createPropertyOperator('123.45', $field);
        $field->validate($operator);
        $this->assertEquals(123.45, $operator->value);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Number must have no more than 2 decimal places');
        $field->validate($this->createPropertyOperator('123.456', $field));
    }

    /**
     * 不正な入力値に対するバリデーションをテスト
     *
     * 数値として解釈できない文字列'abc'を入力した場合:
     * - ValidationExceptionが発生し、'Must be numeric'というメッセージが表示される
     */
    #[Test]
    public function invalidInputValidation(): void
    {
        $field = new DecimalField();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be numeric');
        $field->validate($this->createPropertyOperator('abc', $field));
    }

    /**
     * 複数の制約を組み合わせたバリデーションをテスト
     *
     * maxDigits=5, decimalPlaces=2の場合:
     * 1. 条件を満たす'12.50'は許可される
     * 2. 合計桁数が6桁になる'123.456'はValidationExceptionを発生
     *    - この場合、最大桁数の制約に違反するため、そのエラーメッセージが優先される
     */
    #[Test]
    public function combinedConstraintsValidation(): void
    {
        $field = new DecimalField(maxDigits: 5, decimalPlaces: 2);

        $operator = $this->createPropertyOperator('12.50', $field);
        $field->validate($operator);
        $this->assertEquals(12.50, $operator->value);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Number must have no more than 5 digits in total');
        $field->validate($this->createPropertyOperator('123.456', $field)); // 6 digits in total
    }
}
