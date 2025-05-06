<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\DecimalField;
use PhpValueObject\Core\Validators\DecimalValidator;
use PhpValueObject\Core\Validators\Validatorable;

class DecimalFieldValidateTestClass
{
    public float $prop;
}

/**
 * DecimalFieldのテストクラス
 *
 * DecimalFieldクラスのgetValidatorメソッドが適切に動作することを確認するためのテスト。
 * getValidatorメソッドはDecimalValidatorインスタンスを返し、これを使用して小数値の検証を行えることを確認します。
 */
class DecimalFieldTest extends TestCase
{
    /**
     * デフォルト設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - DecimalFieldのgetValidatorメソッドがDecimalValidatorクラスの名前（文字列）を返すこと
     */
    #[Test]
    public function testGetValidatorReturnsDecimalValidator(): void
    {
        $field = new DecimalField();
        $validator = $field->getValidator();

        $this->assertEquals(DecimalValidator::class, $validator);
    }

    /**
     * カスタム設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - maxDigits, decimalPlaces, gt, lt などの制約を持つDecimalFieldから
     *   getValidatorメソッドを呼び出しても、正しくDecimalValidatorクラスの名前（文字列）が返されること
     *
     * 設定値:
     * - maxDigits: 5 (合計桁数の制限が5桁)
     * - decimalPlaces: 2 (小数点以下の桁数制限が2桁)
     * - gt: 0 (0より大きい値のみ許可)
     * - lt: 100 (100未満の値のみ許可)
     *
     * これらの制約を組み合わせると、実質的に0より大きく100未満の、最大5桁（小数点以下は最大2桁）の小数値のみが許可されます。
     */
    #[Test]
    public function testGetValidatorWithCustomConfigurationReturnsDecimalValidator(): void
    {
        $field = new DecimalField(maxDigits: 5, decimalPlaces: 2, gt: 0, lt: 100);

        $validator = $field->getValidator();

        $this->assertEquals(DecimalValidator::class, $validator);
    }
}
