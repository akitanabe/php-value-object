<?php

declare(strict_types=1);

namespace Test\Support;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\PropertyValue;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Support\SystemValidatorFactory;
use PhpValueObject\Validators\PrimitiveTypeValidator;
use PhpValueObject\Validators\PropertyInitializedValidator;
use PhpValueObject\Validators\PropertyTypeValidator;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Enums\PropertyValueType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * SystemValidatorFactoryクラスのテスト
 *
 * このクラスは、BaseModelで使用されるシステムバリデータを生成する責任を持つ
 * SystemValidatorFactoryクラスの機能をテストします。
 * このテストではモックを使わず、実際のオブジェクトを使用しています。
 */
class SystemValidatorFactoryTest extends TestCase
{
    /**
     * @var ModelConfig
     */
    private ModelConfig $modelConfig;

    /**
     * @var FieldConfig
     */
    private FieldConfig $fieldConfig;

    /**
     * @var PropertyMetadata
     */
    private PropertyMetadata $metadata;

    /**
     * テスト前の共通セットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->modelConfig = new ModelConfig();
        $this->fieldConfig = new FieldConfig();

        // PropertyMetadataの正しい初期化
        $this->metadata = new PropertyMetadata(
            class: 'TestClass',
            name: 'testProperty',
            typeHints: [new TypeHint(TypeHintType::STRING, true, false)],
            initializedStatus: PropertyInitializedStatus::BY_INPUT,
        );
    }

    /**
     * コンストラクタが適切なバリデータを保持することを確認します
     *
     * このテストでは、SystemValidatorFactoryのコンストラクタが渡された
     * バリデータ配列を正しく保持することを検証します。
     */
    #[Test]
    public function constructorCreatesValidators(): void
    {
        // 標準バリデータの配列を作成
        $validators = [
            new PropertyInitializedValidator($this->modelConfig, $this->fieldConfig, $this->metadata),
            new PropertyTypeValidator($this->modelConfig, $this->fieldConfig, $this->metadata),
            new PrimitiveTypeValidator($this->metadata),
        ];

        // テスト対象のインスタンス作成
        $builder = new SystemValidatorFactory($validators);

        // 内部で保持されているバリデータの検証
        $storedValidators = $builder->getValidators();

        $this->assertCount(3, $storedValidators);
        $this->assertInstanceOf(PropertyInitializedValidator::class, $storedValidators[0]);
        $this->assertInstanceOf(PropertyTypeValidator::class, $storedValidators[1]);
        $this->assertInstanceOf(PrimitiveTypeValidator::class, $storedValidators[2]);
    }

    /**
     * createForPropertyメソッドが適切にインスタンスを返すことを確認します
     *
     * このテストでは、静的ファクトリメソッドcreateForPropertyが
     * PropertyOperator、ModelConfig、FieldConfigを受け取り、
     * 正しくSystemValidatorFactoryインスタンスを生成して返すことを検証します。
     */
    #[Test]
    public function createForPropertyReturnsInstance(): void
    {
        $propertyValue = new PropertyValue('testValue', PropertyValueType::STRING);

        $propertyOperator = new PropertyOperator(metadata: $this->metadata, value: $propertyValue);

        // テスト対象のメソッド実行
        $builder = SystemValidatorFactory::createForProperty(
            $propertyOperator,
            $this->modelConfig,
            $this->fieldConfig,
        );

        // 返り値の検証
        $this->assertInstanceOf(SystemValidatorFactory::class, $builder);

        // 内部で作成されたバリデータの検証
        $validators = $builder->getValidators();

        $this->assertCount(3, $validators);
        $this->assertInstanceOf(PropertyInitializedValidator::class, $validators[0]);
        $this->assertInstanceOf(PropertyTypeValidator::class, $validators[1]);
        $this->assertInstanceOf(PrimitiveTypeValidator::class, $validators[2]);
    }

    /**
     * getValidatorsメソッドが正しいバリデータ配列を返すことを確認します
     *
     * このテストでは、getValidatorsメソッドが3つの標準バリデータを含む
     * 配列を正しい順序で返すことを検証します。
     */
    #[Test]
    public function getValidatorsReturnsCorrectArray(): void
    {
        // 標準バリデータの配列を作成
        $validators = [
            new PropertyInitializedValidator($this->modelConfig, $this->fieldConfig, $this->metadata),
            new PropertyTypeValidator($this->modelConfig, $this->fieldConfig, $this->metadata),
            new PrimitiveTypeValidator($this->metadata),
        ];

        // テスト対象のインスタンス作成
        $builder = new SystemValidatorFactory($validators);

        // 返り値の検証
        $storedValidators = $builder->getValidators();

        $this->assertCount(3, $storedValidators);
        $this->assertInstanceOf(PropertyInitializedValidator::class, $storedValidators[0]);
        $this->assertInstanceOf(PropertyTypeValidator::class, $storedValidators[1]);
        $this->assertInstanceOf(PrimitiveTypeValidator::class, $storedValidators[2]);
    }
}
