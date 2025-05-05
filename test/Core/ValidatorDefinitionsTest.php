<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core;

use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Core\ValidatorDefinitions;
use PHPUnit\Framework\TestCase;
use stdClass;

class ValidatorDefinitionsTest extends TestCase
{
    /**
     * バリデータの登録と取得の基本機能をテストする
     * register()メソッドの戻り値とget()メソッドの動作を確認
     */
    public function testRegisterAndGetValidator(): void
    {
        // SetUp
        $validatorDefinitions = new ValidatorDefinitions();
        $modelConfig = new ModelConfig(allowUninitializedProperty: false, allowNoneTypeProperty: false);

        // register
        $result = $validatorDefinitions->register($modelConfig);

        // 戻り値はValidatorDefinitionsのインスタンス（メソッドチェーン用）
        $this->assertInstanceOf(ValidatorDefinitions::class, $result);

        // 登録したバリデータを取得できること
        $storedValidator = $validatorDefinitions->get(ModelConfig::class);
        $this->assertSame($modelConfig, $storedValidator);
    }

    /**
     * バリデータの存在確認機能をテストする
     * has()メソッドがバリデータの登録状態を正しく返すことを確認
     */
    public function testHasValidator(): void
    {
        // SetUp
        $validatorDefinitions = new ValidatorDefinitions();
        $modelConfig = new ModelConfig(allowMixedTypeProperty: true);

        // 登録前はfalseを返すこと
        $this->assertFalse($validatorDefinitions->has(ModelConfig::class));

        // 登録
        $validatorDefinitions->register($modelConfig);

        // 登録後はtrueを返すこと
        $this->assertTrue($validatorDefinitions->has(ModelConfig::class));
    }

    /**
     * 存在しないバリデータを取得した場合の挙動をテストする
     * 未登録クラスに対してget()メソッドが正しくnullを返すことを確認
     */
    public function testGetNonExistentValidator(): void
    {
        // SetUp
        $validatorDefinitions = new ValidatorDefinitions();

        // 存在しないバリデータを取得するとnullが返ること
        // @phpstan-ignore argument.type (存在しないクラスを指定)
        $this->assertNull($validatorDefinitions->get('NonExistentClass'));
    }

    /**
     * 複数の異なる型のバリデータを登録・取得する機能をテストする
     * 複数オブジェクトが独立して登録・取得できることを確認
     */
    public function testRegisterMultipleValidators(): void
    {
        // SetUp
        $validatorDefinitions = new ValidatorDefinitions();
        $modelConfig = new ModelConfig(allowUninitializedProperty: true);
        $object = new stdClass();

        // 異なる型のオブジェクトを登録
        $validatorDefinitions
            ->register($modelConfig)
            ->register($object);

        // 全て正しく取得できること
        $this->assertSame($modelConfig, $validatorDefinitions->get(ModelConfig::class));
        $this->assertSame($object, $validatorDefinitions->get(stdClass::class));
    }

    /**
     * 同じ型のバリデータを上書き登録する機能をテストする
     * 同じクラス型の場合に後から登録したオブジェクトが優先されることを確認
     */
    public function testOverwriteValidator(): void
    {
        // SetUp
        $validatorDefinitions = new ValidatorDefinitions();
        $modelConfig1 = new ModelConfig(allowUninitializedProperty: true);
        $modelConfig2 = new ModelConfig(allowInheritableClass: false);

        // 同じ型のオブジェクトを2回登録（上書き）
        $validatorDefinitions->register($modelConfig1);
        $validatorDefinitions->register($modelConfig2);

        // 後から登録したオブジェクトが取得できること
        $this->assertSame($modelConfig2, $validatorDefinitions->get(ModelConfig::class));
        $this->assertNotSame($modelConfig1, $validatorDefinitions->get(ModelConfig::class));
    }
}
