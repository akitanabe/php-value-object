<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Validators\PropertyInitializedValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PropertyInitializedValidatorTest extends TestCase
{
    /**
     * 初期化済みプロパティの場合、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForInitializedProperty(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyInitializedStatus::BY_DEFAULT);
        $modelConfig = new ModelConfig(false);
        $fieldConfig = new FieldConfig(false);

        $validator = new PropertyInitializedValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * 未初期化プロパティが許可されている場合（モデルレベル）、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForUninitializedPropertyAllowedByModel(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyInitializedStatus::UNINITIALIZED);
        $modelConfig = new ModelConfig(true); // 未初期化プロパティを許可
        $fieldConfig = new FieldConfig(false);

        $validator = new PropertyInitializedValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * 未初期化プロパティが許可されている場合（フィールドレベル）、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForUninitializedPropertyAllowedByField(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyInitializedStatus::UNINITIALIZED);
        $modelConfig = new ModelConfig(false);
        $fieldConfig = new FieldConfig(true); // 未初期化プロパティを許可

        $validator = new PropertyInitializedValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * 未初期化プロパティが許可されていない場合、例外をスローすることを確認
     */
    #[Test]
    public function testValidateThrowsExceptionForUninitializedPropertyNotAllowed(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyInitializedStatus::UNINITIALIZED);
        $modelConfig = new ModelConfig(false);
        $fieldConfig = new FieldConfig(false);

        $validator = new PropertyInitializedValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $this->expectException(InvalidPropertyStateException::class);
        $validator->validate($value);
    }

    /**
     * 指定した初期化状態のPropertyMetadataインスタンスを作成
     */
    private function createPropertyMetadata(PropertyInitializedStatus $status): PropertyMetadata
    {
        // PropertyMetadataの正しいコンストラクタ引数順序で初期化
        return new PropertyMetadata(
            'TestClass',
            'testProperty',
            [], // typeHints
            $status,
        );
    }
}
