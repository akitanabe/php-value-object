<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Support;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Support\PropertyOperator;
use PhpValueObject\Support\PropertyValue;
use PhpValueObject\Fields\Field;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Support\SystemValidatorFactory;
use PhpValueObject\Validators\PrimitiveTypeValidator;
use PhpValueObject\Validators\InitializationStateValidator;
use PhpValueObject\Validators\NoneTypeValidator;
use PhpValueObject\Validators\MixedTypeValidator;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Enums\PropertyValueType;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PhpValueObject\Validators\IdenticalValidator;

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
     * @var Field
     */
    private Field $field;

    /**
     * テスト前の共通セットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->field = new Field();

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
        // preValidator と standardValidator の配列を作成
        $preValidators = [
            new InitializationStateValidator($this->modelConfig, $this->fieldConfig, $this->metadata),
        ];
        $standardValidators = [new PrimitiveTypeValidator($this->metadata), $this->field->getValidator(),];

        // テスト対象のインスタンス作成
        $builder = new SystemValidatorFactory($preValidators, $standardValidators);

        // 内部で保持されているバリデータの検証
        $storedPreValidators = $builder->getPreValidators();
        $storedStandardValidators = $builder->getStandardValidators();

        $this->assertCount(1, $storedPreValidators);
        $this->assertInstanceOf(InitializationStateValidator::class, $storedPreValidators[0]);

        $this->assertCount(2, $storedStandardValidators);
        $this->assertInstanceOf(PrimitiveTypeValidator::class, $storedStandardValidators[0]);
        $this->assertInstanceOf(
            IdenticalValidator::class,
            $storedStandardValidators[1],
        ); // Field->getValidator() は IdenticalValidator を返す
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
            $this->field,
        );

        // 返り値の検証
        $this->assertInstanceOf(SystemValidatorFactory::class, $builder);

        // 内部で作成されたバリデータの検証 (preValidators)
        $preValidators = $builder->getPreValidators();
        $this->assertCount(3, $preValidators);
        $this->assertInstanceOf(InitializationStateValidator::class, $preValidators[0]);
        $this->assertInstanceOf(NoneTypeValidator::class, $preValidators[1]);
        $this->assertInstanceOf(MixedTypeValidator::class, $preValidators[2]);

        // 内部で作成されたバリデータの検証 (standardValidators)
        $standardValidators = $builder->getStandardValidators();
        $this->assertCount(2, $standardValidators);
        $this->assertInstanceOf(PrimitiveTypeValidator::class, $standardValidators[0]);
        $this->assertInstanceOf(
            IdenticalValidator::class,
            $standardValidators[1],
        ); // Field->getValidator() は IdenticalValidator を返す
    }

    /**
     * getterメソッドが正しいバリデータ配列を返すことを確認します
     *
     * このテストでは、getPreValidators と getStandardValidators メソッドが
     * それぞれ正しいバリデータを含む配列を返すことを検証します。
     */
    #[Test]
    public function gettersReturnCorrectArrays(): void
    {
        // preValidator と standardValidator の配列を作成
        $preValidators = [
            new InitializationStateValidator($this->modelConfig, $this->fieldConfig, $this->metadata),
        ];
        $standardValidators = [new PrimitiveTypeValidator($this->metadata), $this->field->getValidator(),];

        // テスト対象のインスタンス作成
        $builder = new SystemValidatorFactory($preValidators, $standardValidators);

        // getPreValidators の返り値検証
        $storedPreValidators = $builder->getPreValidators();
        $this->assertCount(1, $storedPreValidators);
        $this->assertInstanceOf(InitializationStateValidator::class, $storedPreValidators[0]);
        $this->assertSame(
            $preValidators,
            $storedPreValidators,
            'getPreValidators should return the exact preValidators array',
        );

        // getStandardValidators の返り値検証
        $storedStandardValidators = $builder->getStandardValidators();
        $this->assertCount(2, $storedStandardValidators);
        $this->assertInstanceOf(PrimitiveTypeValidator::class, $storedStandardValidators[0]);
        $this->assertInstanceOf(IdenticalValidator::class, $storedStandardValidators[1]);
        $this->assertSame(
            $standardValidators,
            $storedStandardValidators,
            'getStandardValidators should return the exact standardValidators array',
        );
    }
}
