<?php

declare(strict_types=1);

namespace PhSculptis\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhSculptis\Fields\StringField;
use PhSculptis\Core\Validators\StringValidator;
use PhSculptis\Core\Definitions\StringValidatorDefinition;

class StringFieldValidateTestClass
{
    public string $prop;
}

/**
 * StringFieldクラスのテスト
 *
 * StringFieldクラスのgetValidatorメソッドが適切に動作することを確認するためのテスト。
 * getValidatorメソッドはStringValidatorクラス名（文字列）を返し、これを使用して文字列の検証を行えることを確認します。
 */
class StringFieldTest extends TestCase
{
    /**
     * デフォルト設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - StringFieldのgetValidatorメソッドがStringValidatorクラスの名前（文字列）を返すこと
     */
    #[Test]
    public function testGetValidatorReturnsStringValidator(): void
    {
        $field = new StringField();
        $validator = $field->getValidator();

        $this->assertEquals(StringValidator::class, $validator);
    }

    /**
     * getDefinitionメソッドが適切なStringValidatorDefinitionを返すことをテスト
     *
     * 検証内容:
     * - StringFieldのgetDefinitionメソッドが適切なStringValidatorDefinitionオブジェクトを返すこと
     */
    #[Test]
    public function testGetDefinitionReturnsStringValidatorDefinition(): void
    {
        $field = new StringField(allowEmpty: false, minLength: 5, maxLength: 10, pattern: '/^[a-z]+$/');
        $definition = $field->getDefinition();

        $this->assertInstanceOf(StringValidatorDefinition::class, $definition);
        $this->assertFalse($definition->allowEmpty);
        $this->assertEquals(5, $definition->minLength);
        $this->assertEquals(10, $definition->maxLength);
        $this->assertEquals('/^[a-z]+$/', $definition->pattern);
    }
}
