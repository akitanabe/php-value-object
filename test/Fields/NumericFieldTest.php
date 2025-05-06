<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\NumericField;
use PhpValueObject\Core\Validators\NumericValidator;

/**
 * NumericFieldクラスのテスト
 *
 * NumericFieldクラスのgetValidatorメソッドが適切に動作することを確認するためのテスト。
 * getValidatorメソッドはNumericValidatorクラス名（文字列）を返し、これを使用して数値の検証を行えることを確認します。
 */
class NumericFieldTest extends TestCase
{
    /**
     * デフォルト設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - NumericFieldのgetValidatorメソッドがNumericValidatorクラスの名前（文字列）を返すこと
     */
    #[Test]
    public function testGetValidatorReturnsNumericValidator(): void
    {
        $field = new NumericField();
        $validator = $field->getValidator();

        $this->assertEquals(NumericValidator::class, $validator);
    }

    /**
     * カスタム設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - gt, lt, ge, leなどの数値範囲制約を持つNumericFieldから
     *   getValidatorメソッドを呼び出しても、正しくNumericValidatorクラスの名前（文字列）が返されること
     *
     * 設定値:
     * - gt: 10 (10より大きい値のみ許可)
     * - lt: 100 (100未満の値のみ許可)
     * - ge: 20 (20以上の値のみ許可)
     * - le: 80 (80以下の値のみ許可)
     *
     * これらの制約を組み合わせると、実質的に20以上80以下の範囲の値のみが許可されます。
     */
    #[Test]
    public function testGetValidatorWithCustomConfigurationReturnsNumericValidator(): void
    {
        $field = new NumericField(gt: 10, lt: 100, ge: 20, le: 80);

        $validator = $field->getValidator();

        $this->assertEquals(NumericValidator::class, $validator);
    }
}
