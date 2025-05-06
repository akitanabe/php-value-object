<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\NumericField;
use PhpValueObject\Core\Validators\NumericValidator;
use PhpValueObject\Core\Definitions\NumericValidatorDefinition;

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
     * getDefinitionメソッドが適切なNumericValidatorDefinitionを返すことをテスト
     *
     * 検証内容:
     * - NumericFieldのgetDefinitionメソッドが適切なNumericValidatorDefinitionオブジェクトを返すこと
     */
    #[Test]
    public function testGetDefinitionReturnsNumericValidatorDefinition(): void
    {
        $field = new NumericField(gt: 0, lt: 100, ge: 1, le: 99);
        $definition = $field->getDefinition();

        $this->assertInstanceOf(NumericValidatorDefinition::class, $definition);
        $this->assertEquals(0, $definition->gt);
        $this->assertEquals(100, $definition->lt);
        $this->assertEquals(1, $definition->ge);
        $this->assertEquals(99, $definition->le);
    }
}
