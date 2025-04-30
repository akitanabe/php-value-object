<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\NumericField;
use PhpValueObject\Core\Validators\NumericValidator;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * NumericFieldクラスのテスト
 *
 * NumericFieldクラスのgetValidatorメソッドが適切に動作することを確認するためのテスト。
 * getValidatorメソッドはNumericValidatorインスタンスを返し、これを使用して数値の検証を行えることを確認します。
 */
class NumericFieldTest extends TestCase
{
    /**
     * デフォルト設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - NumericFieldのgetValidatorメソッドがNumericValidatorクラスのインスタンスを返すこと
     * - 返されるオブジェクトがValidatorableインターフェースを実装していること
     */
    #[Test]
    public function testGetValidatorReturnsNumericValidator(): void
    {
        $field = new NumericField();
        $validator = $field->getValidator();

        $this->assertInstanceOf(NumericValidator::class, $validator);
        $this->assertInstanceOf(Validatorable::class, $validator);
    }

    /**
     * カスタム設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - gt, lt, ge, leなどの数値範囲制約を持つNumericFieldから
     *   getValidatorメソッドを呼び出しても、正しくNumericValidatorクラスのインスタンスが返されること
     * - 返されるオブジェクトがValidatorableインターフェースを実装していること
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

        $this->assertInstanceOf(NumericValidator::class, $validator);
        $this->assertInstanceOf(Validatorable::class, $validator);
    }
}
