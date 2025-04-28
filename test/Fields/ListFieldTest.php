<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Fields;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Fields\ListField;
use PhpValueObject\Core\Validators\ListValidator;
use PhpValueObject\Validators\Validatorable;

class ListFieldValidateTestClass
{
    // @phpstan-ignore missingType.iterableValue
    public array $prop;
}

/**
 * ListFieldクラスのテスト
 *
 * ListFieldクラスのgetValidatorメソッドが適切に動作することを確認するためのテスト。
 * getValidatorメソッドはListValidatorインスタンスを返し、これを使用して配列（リスト）の検証を行えることを確認します。
 */
class ListFieldTest extends TestCase
{
    /**
     * デフォルト設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - ListFieldのgetValidatorメソッドがListValidatorクラスのインスタンスを返すこと
     * - 返されるオブジェクトがValidatorableインターフェースを実装していること
     *
     * デフォルト設定では、配列（リスト）の形式のみをチェックし、要素の型については検証しません。
     */
    #[Test]
    public function testGetValidatorReturnsListValidator(): void
    {
        $field = new ListField();
        $validator = $field->getValidator();

        $this->assertInstanceOf(ListValidator::class, $validator);
        $this->assertInstanceOf(Validatorable::class, $validator);
    }

    /**
     * 型指定ありの設定でのgetValidatorメソッドの動作をテスト
     *
     * 検証内容:
     * - 要素の型（string）を指定したListFieldからgetValidatorメソッドを呼び出しても、
     *   正しくListValidatorクラスのインスタンスが返されること
     * - 返されるオブジェクトがValidatorableインターフェースを実装していること
     *
     * 'string'型の指定ありの場合、配列内の全要素が文字列型であることを検証します。
     */
    #[Test]
    public function testGetValidatorWithTypeConfigurationReturnsListValidator(): void
    {
        $field = new ListField(type: 'string');

        $validator = $field->getValidator();

        $this->assertInstanceOf(ListValidator::class, $validator);
        $this->assertInstanceOf(Validatorable::class, $validator);
    }
}
