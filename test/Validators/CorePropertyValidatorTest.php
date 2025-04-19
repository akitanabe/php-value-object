<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Validators\CorePropertyValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;

/**
 * CorePropertyValidatorのテスト用具象クラス
 */
class TestCorePropertyValidator extends CorePropertyValidator
{
    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        return $value;
    }
}

class CorePropertyValidatorTest extends TestCase
{
    /**
     * デフォルトでINTERNALモードを使用することを確認
     */
    #[Test]
    public function testUsesInternalModeByDefault(): void
    {
        $metadata = $this->createPropertyMetadata();
        $validator = new TestCorePropertyValidator($metadata);

        $this->assertEquals(ValidatorMode::INTERNAL, $validator->getMode());
    }

    /**
     * コンストラクタで指定したモードを使用することを確認
     */
    #[Test]
    public function testUsesSpecifiedMode(): void
    {
        $metadata = $this->createPropertyMetadata();
        $validator = new TestCorePropertyValidator($metadata, ValidatorMode::AFTER);

        $this->assertEquals(ValidatorMode::AFTER, $validator->getMode());
    }

    /**
     * getMetadataメソッドが設定したメタデータを返すことを確認
     */
    #[Test]
    public function testGetMetadataReturnsPropertyMetadata(): void
    {
        $metadata = $this->createPropertyMetadata();
        $validator = new TestCorePropertyValidator($metadata);

        $this->assertSame($metadata, $validator->getMetadata());
    }

    /**
     * テスト用のPropertyMetadataインスタンスを作成
     */
    private function createPropertyMetadata(): PropertyMetadata
    {
        // プロパティのリフレクションを取得するためのダミークラス
        $dummy = new class {
            public string $testProperty;
        };

        $refProperty = new ReflectionProperty($dummy, 'testProperty');

        // PropertyMetadataの正しいコンストラクタ引数順序で初期化
        return new PropertyMetadata(
            get_class($dummy),
            'testProperty',
            [], // typeHints
            PropertyInitializedStatus::UNINITIALIZED,
        );
    }
}
