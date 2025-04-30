<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\StringField;
use PhpValueObject\Core\Validators\StringValidator;
use PhpValueObject\Core\Validators\Validatorable;

class StringFieldValidateTestClass
{
    public string $prop;
}

/**
 * StringFieldクラスのテスト
 *
 * StringFieldクラスのgetValidatorメソッドが適切に動作することを確認するためのテスト。
 * getValidatorメソッドはStringValidatorインスタンスを返し、これを使用して文字列の検証を行えることを確認します。
 */
class StringFieldTest extends TestCase
{
    /**
     * デフォルト設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - StringFieldのgetValidatorメソッドがStringValidatorクラスのインスタンスを返すこと
     * - 返されるオブジェクトがValidatorableインターフェースを実装していること
     */
    #[Test]
    public function testGetValidatorReturnsStringValidator(): void
    {
        $field = new StringField();
        $validator = $field->getValidator();

        $this->assertInstanceOf(StringValidator::class, $validator);
        $this->assertInstanceOf(Validatorable::class, $validator);
    }

    /**
     * カスタム設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - allowEmpty, minLength, maxLength, patternなどのカスタム設定を持つStringFieldから
     *   getValidatorメソッドを呼び出しても、正しくStringValidatorクラスのインスタンスが返されること
     * - 返されるオブジェクトがValidatorableインターフェースを実装していること
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

        $this->assertInstanceOf(StringValidator::class, $validator);
        $this->assertInstanceOf(Validatorable::class, $validator);
    }
}
