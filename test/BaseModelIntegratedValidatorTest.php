<?php

declare(strict_types=1);

namespace PhpValueObject\Test;

use PhpValueObject\BaseModel;
use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use TypeError;
use stdClass;

/**
 * BaseModel統合バリデータテスト用のクラス
 * プロパティの型定義：string
 */
#[ModelConfig(false, false, false)]
class TestModelWithStringProperty extends BaseModel
{
    // 未初期化プロパティとして明示的に設定することで、テストをより明確にする
    // デフォルトでは未初期化プロパティは許可されていない
    #[FieldConfig(false, false, false)]
    public string $name;
}

/**
 * BaseModel統合バリデータテスト用のクラス
 * プロパティの型定義：int
 */
#[ModelConfig(false, false, false)]
class TestModelWithIntProperty extends BaseModel
{
    public int $count;
}

/**
 * BaseModel統合バリデータテスト用のクラス
 * mixed型プロパティ（通常は許可されないが、このテストでは許可する設定）
 */
#[ModelConfig(false, false, true)]
class TestModelWithMixedProperty extends BaseModel
{
    public mixed $data;
}

/**
 * None型プロパティを持つテストモデル
 * 型定義のないプロパティ（通常は許可されないが、このテストでは許可する設定）
 */
#[ModelConfig(false, true, false)]
class TestModelWithNoneTypeProperty extends BaseModel
{
    #[FieldConfig(false, true, false)]
    // @phpstan-ignore missingType.property (None型プロパティのテスト)
    public $data;
}

/**
 * BaseModelの統合バリデータ機能をテストするクラス
 */
class BaseModelIntegratedValidatorTest extends TestCase
{
    /**
     * 正しい型のデータでの初期化テスト
     */
    #[Test]
    public function testInitializeWithValidType(): void
    {
        // string型プロパティに文字列を設定
        $model = TestModelWithStringProperty::fromArray(['name' => 'test_name']);
        $this->assertEquals('test_name', $model->name);

        // int型プロパティに整数を設定
        $model = TestModelWithIntProperty::fromArray(['count' => 123]);
        $this->assertEquals(123, $model->count);
    }

    /**
     * 型が一致しない場合の初期化テスト
     */
    #[Test]
    public function testInitializeWithInvalidType(): void
    {
        // string型プロパティに数値を設定すると型エラーが発生する
        $this->expectException(TypeError::class);
        TestModelWithStringProperty::fromArray(['name' => 123]);
    }

    /**
     * mixed型プロパティのテスト (ModelConfigでmixed型を許可)
     */
    #[Test]
    public function testInitializeWithMixedTypeProperty(): void
    {
        // mixed型には任意の型の値を設定できる
        $model = TestModelWithMixedProperty::fromArray(['data' => 'string_value']);
        $this->assertEquals('string_value', $model->data);

        $model = TestModelWithMixedProperty::fromArray(['data' => 123]);
        $this->assertEquals(123, $model->data);

        $model = TestModelWithMixedProperty::fromArray(['data' => true]);
        $this->assertTrue($model->data);

        $obj = new stdClass();
        $obj->test = 'value';
        $model = TestModelWithMixedProperty::fromArray(['data' => $obj]);
        $this->assertSame($obj, $model->data);
    }

    /**
     * 型定義のないプロパティのテスト (ModelConfigとFieldConfigでnone型を許可)
     */
    #[Test]
    public function testInitializeWithNoneTypeProperty(): void
    {
        // 型定義のないプロパティには任意の型の値を設定できる
        $model = TestModelWithNoneTypeProperty::fromArray(['data' => 'string_value']);
        $this->assertEquals('string_value', $model->data);

        $model = TestModelWithNoneTypeProperty::fromArray(['data' => 123]);
        $this->assertEquals(123, $model->data);
    }

    /**
     * 未初期化プロパティのテスト
     */
    #[Test]
    public function testUninitializedProperty(): void
    {
        // 必須プロパティを初期化せずにモデルを作成すると例外が発生する
        $this->expectException(InvalidPropertyStateException::class);
        $this->expectExceptionMessage('is not initialized');
        TestModelWithStringProperty::fromArray([]);
    }
}
