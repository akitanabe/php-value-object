<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Core\Validators\StringValidator;

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
     * カスタム設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - allowEmpty, minLength, maxLength, patternなどのカスタム設定を持つStringFieldから
     *   getValidatorメソッドを呼び出しても、正しくStringValidatorクラスの名前（文字列）が返されること
     *
     * 設定値:
     * - allowEmpty: false (空文字列を許可しない)
     * - minLength: 5 (最小文字数5文字)
     * - maxLength: 10 (最大文字数10文字)
     * - pattern: '/^[a-z]+$/' (小文字アルファベットのみを許可)
     */
    #[Test]
    public function testGetValidatorWithCustomConfigurationReturnsStringValidator(): void
    {
        $field = new StringField(allowEmpty: false, minLength: 5, maxLength: 10, pattern: '/^[a-z]+$/');

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

        $this->assertIsObject($definition);
        $this->assertInstanceOf(\PhpValueObject\Core\Definitions\StringValidatorDefinition::class, $definition);
        $this->assertFalse($definition->allowEmpty);
        $this->assertEquals(5, $definition->minLength);
        $this->assertEquals(10, $definition->maxLength);
        $this->assertEquals('/^[a-z]+$/', $definition->pattern);
    }
}
