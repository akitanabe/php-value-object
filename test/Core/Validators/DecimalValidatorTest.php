<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PHPUnit\Framework\TestCase;
use PhpValueObject\Core\Validators\DecimalValidator;
use PhpValueObject\Exceptions\ValidationException;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Core\Validators\Validatorable;
use PhpValueObject\Helpers\ValidatorHelper;

/**
 * DecimalValidatorのテストクラス
 *
 * このクラスでは、小数値に関するバリデーション機能を検証します。
 * - 数値形式の検証
 * - 桁数の検証（最大桁数、小数点以下の桁数）
 * - 値の範囲検証（最小値・最大値）
 * - バリデーションハンドラーの動作
 */
class DecimalValidatorTest extends TestCase
{
    /**
     * 有効な小数値が正しく検証されることをテスト
     *
     * 小数値（123.45）を渡した場合、そのまま値が返されることを確認します。
     */
    public function testValidateReturnsDecimalWhenValid(): void
    {
        $validator = new DecimalValidator();
        $result = $validator->validate(123.45);

        $this->assertSame(123.45, $result);
    }

    /**
     * 整数値が小数値として正しく検証されることをテスト
     *
     * 整数値（123）も小数値のバリデーションを通過することを確認します。
     */
    public function testValidateAcceptsIntegerAsValidDecimal(): void
    {
        $validator = new DecimalValidator();
        $result = $validator->validate(123);

        $this->assertSame(123, $result);
    }

    /**
     * 数値形式の文字列が正しく検証されることをテスト
     *
     * 数値形式の文字列（'123.45'）が小数値として正しく検証されることを確認します。
     */
    public function testValidateAcceptsNumericString(): void
    {
        $validator = new DecimalValidator();
        $result = $validator->validate('123.45');

        $this->assertSame('123.45', $result);
    }

    /**
     * 数値以外の値が例外をスローすることをテスト
     *
     * 数値以外の値（'not a number'）が渡された場合に、ValidationExceptionがスローされることを確認します。
     */
    public function testValidateThrowsExceptionWhenValueIsNotNumeric(): void
    {
        $validator = new DecimalValidator();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Must be numeric');

        $validator->validate('not a number');
    }

    /**
     * 合計桁数が最大桁数を超える場合に例外がスローされることをテスト
     *
     * 合計桁数が最大桁数（4桁）を超える値（12345.67）が渡された場合に、
     * ValidationExceptionがスローされることを確認します。
     */
    public function testValidateThrowsExceptionWhenTotalDigitsExceedMaxDigits(): void
    {
        $validator = new DecimalValidator(maxDigits: 4);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Number must have no more than 4 digits in total');

        $validator->validate(12345.67);
    }

    /**
     * 最大桁数の検証が整数部と小数部の両方を含むことをテスト
     *
     * maxDigitsパラメータが整数部と小数部の合計桁数を正しく検証することを確認します。
     * - 123.45: 整数部3桁 + 小数部2桁 = 合計5桁
     * - 1.2345: 整数部1桁 + 小数部4桁 = 合計5桁
     * - 123.456: 整数部3桁 + 小数部3桁 = 合計6桁（maxDigits=5を超えるためエラー）
     */
    public function testMaxDigitsIncludesIntegerAndDecimalDigits(): void
    {
        $validator = new DecimalValidator(maxDigits: 5);

        // 3 digits before decimal point, 2 after (total: 5) - should pass
        $this->assertSame(123.45, $validator->validate(123.45));

        // Test with a different value (1 digit before, 4 after) - should also pass
        $this->assertSame(1.2345, $validator->validate(1.2345));

        // 3 digits before, 3 after (total: 6) - should fail
        $this->expectException(ValidationException::class);
        $validator->validate(123.456);
    }

    /**
     * 負の数値に対する桁数検証をテスト
     *
     * 負の数値の場合でも、マイナス記号は桁数にカウントされないことを確認します。
     * - -123.4: 整数部3桁 + 小数部1桁 = 合計4桁
     * - -1234.5: 整数部4桁 + 小数部1桁 = 合計5桁（maxDigits=4を超えるためエラー）
     */
    public function testMaxDigitsWithNegativeNumber(): void
    {
        $validator = new DecimalValidator(maxDigits: 4);

        // Negative sign should not count towards digit count
        $this->assertSame(-123.4, $validator->validate(-123.4));

        $this->expectException(ValidationException::class);
        $validator->validate(-1234.5); // 5 digits total (excluding negative sign)
    }

    /**
     * 小数点以下の桁数が制限を超える場合に例外がスローされることをテスト
     *
     * 小数点以下の桁数が制限（2桁）を超える値（123.456）が渡された場合に、
     * ValidationExceptionがスローされることを確認します。
     */
    public function testValidateThrowsExceptionWhenDecimalPlacesExceedLimit(): void
    {
        $validator = new DecimalValidator(decimalPlaces: 2);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invalid Field Value. Number must have no more than 2 decimal places');

        $validator->validate(123.456);
    }

    /**
     * decimalPlacesパラメータが小数点以下の桁数のみを検証することをテスト
     *
     * decimalPlacesパラメータが小数点以下の桁数のみを検証し、整数部の桁数は
     * 関係ないことを確認します。
     * - 123.456: 小数部3桁（OK - 制限も3桁）
     * - 12345: 小数部なし（OK - 小数点以下がない場合は常に有効）
     * - 123.4567: 小数部4桁（NG - 制限の3桁を超える）
     */
    public function testDecimalPlacesOnlyCountsDigitsAfterDecimalPoint(): void
    {
        $validator = new DecimalValidator(decimalPlaces: 3);

        // 3 decimal places - should pass
        $this->assertSame(123.456, $validator->validate(123.456));

        // No decimal places - should pass
        $this->assertSame(12345, $validator->validate(12345));

        // 4 decimal places - should fail
        $this->expectException(ValidationException::class);
        $validator->validate(123.4567);
    }

    /**
     * 全ての数値範囲バリデーションをサポートしていることをテスト
     *
     * DecimalValidatorが以下の数値範囲のバリデーションをサポートしていることを確認します：
     * - gt (greater than): 指定値より大きい
     * - lt (less than): 指定値より小さい
     * - ge (greater than or equal): 指定値以上
     * - le (less than or equal): 指定値以下
     */
    public function testValidatorSupportsAllNumericRangeValidations(): void
    {
        // Test greater than
        $gtValidator = new DecimalValidator(gt: 100);
        $this->assertSame(100.1, $gtValidator->validate(100.1));

        try {
            $gtValidator->validate(99.9);
            $this->fail('ValidationException should have been thrown for value not greater than 100');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Must be greater than 100', $e->getMessage());
        }

        // Test less than
        $ltValidator = new DecimalValidator(lt: 100);
        $this->assertSame(99.9, $ltValidator->validate(99.9));

        try {
            $ltValidator->validate(100.1);
            $this->fail('ValidationException should have been thrown for value not less than 100');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Must be less than 100', $e->getMessage());
        }

        // Test greater than or equal to
        $geValidator = new DecimalValidator(ge: 100);
        $this->assertSame(100.0, $geValidator->validate(100.0));

        try {
            $geValidator->validate(99.9);
            $this->fail('ValidationException should have been thrown for value not greater than or equal to 100');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Must be greater than or equal to 100', $e->getMessage());
        }

        // Test less than or equal to
        $leValidator = new DecimalValidator(le: 100);
        $this->assertSame(100.0, $leValidator->validate(100.0));

        try {
            $leValidator->validate(100.1);
            $this->fail('ValidationException should have been thrown for value not less than or equal to 100');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('Must be less than or equal to 100', $e->getMessage());
        }
    }

    /**
     * 複数の制約条件を組み合わせたバリデーションをテスト
     *
     * 複数の制約条件（最大桁数、小数点以下の桁数、最小値、最大値）を
     * 組み合わせた場合に、正しくバリデーションされることを確認します。
     * 条件：
     * - 最大桁数: 5桁
     * - 小数点以下の桁数: 最大2桁
     * - 最小値: 10以上（ge: 10）
     * - 最大値: 1000未満（lt: 1000）
     */
    public function testValidateWithCombinedConstraints(): void
    {
        $validator = new DecimalValidator(maxDigits: 5, decimalPlaces: 2, ge: 10, lt: 1000);

        // Valid: 5 digits total, 2 decimal places, >= 10, < 1000
        $this->assertSame(123.45, $validator->validate(123.45));

        // Too many total digits
        try {
            $validator->validate(1234.56);
            $this->fail('Should have failed due to too many total digits');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('no more than 5 digits', $e->getMessage());
        }

        // Too many decimal places
        try {
            $validator->validate(12.345);
            $this->fail('Should have failed due to too many decimal places');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('no more than 2 decimal places', $e->getMessage());
        }

        // Less than minimum
        try {
            $validator->validate(9.99);
            $this->fail('Should have failed due to value less than minimum');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('greater than or equal to 10', $e->getMessage());
        }

        // Greater than maximum
        try {
            $validator->validate(1000.0);
            $this->fail('Should have failed due to value greater than maximum');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('less than 1000', $e->getMessage());
        }
    }

    /**
     * ValidatorFunctionWrapHandlerを使用したバリデーションをテスト
     *
     * DecimalValidator::validate()メソッドがValidatorFunctionWrapHandlerを
     * 正しく使用することを確認します。バリデーションハンドラーを通じて
     * 値が変更される場合に、その変更後の値が正しく返されることをテストします。
     *
     * このテストでは、値を2倍にするカスタムバリデータを作成し、
     * ハンドラーを通じて実行されることを検証します。
     */
    public function testValidateUsesValidatorFunctionWrapHandler(): void
    {
        $validator = new DecimalValidator();

        // 実際のValidatorFunctionWrapHandlerを作成
        $mockValidator = new class implements Validatorable {
            public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
            {
                // 値を2倍にする簡単な処理
                return (float) $value * 2;
            }
        };

        // ValidatorHelperを使用してSplQueueを作成
        $validators = ValidatorHelper::createValidatorQueue([$mockValidator]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        $result = $validator->validate(123.45, $handler);

        $this->assertSame(123.45 * 2, $result);
    }
}
