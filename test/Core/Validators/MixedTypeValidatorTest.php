<?php

declare(strict_types=1);

namespace PhSculptis\Test\Core\Validators;

use PhSculptis\Config\FieldConfig;
use PhSculptis\Config\ModelConfig;
use PhSculptis\Enums\PropertyInitializedStatus;
use PhSculptis\Enums\TypeHintType;
use PhSculptis\Exceptions\InvalidPropertyStateException;
use PhSculptis\Support\PropertyMetadata;
use PhSculptis\Support\TypeHint;
use PhSculptis\Core\Validators\MixedTypeValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MixedTypeValidatorTest extends TestCase
{
    /**
     * 有効な型のプロパティの場合、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForValidTypeProperty(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::STRING, true, false)]);
        $modelConfig = new ModelConfig(); // mixed型を許可しない
        $fieldConfig = new FieldConfig(); // mixed型を許可しない

        $validator = new MixedTypeValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * Mixed型が許可されている場合（モデルレベル）、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForMixedTypeAllowedByModel(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::MIXED, false, false)]);
        $modelConfig = new ModelConfig(allowMixedTypeProperty: true); // mixed型を許可
        $fieldConfig = new FieldConfig(); // mixed型を許可しない

        $validator = new MixedTypeValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * Mixed型が許可されている場合（フィールドレベル）、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForMixedTypeAllowedByField(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::MIXED, false, false)]);
        $modelConfig = new ModelConfig(); // mixed型を許可しない
        $fieldConfig = new FieldConfig(allowMixedTypeProperty: true); // mixed型を許可

        $validator = new MixedTypeValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * Mixed型が許可されていない場合、例外をスローすることを確認
     */
    #[Test]
    public function testValidateThrowsExceptionForMixedTypeNotAllowed(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::MIXED, false, false)]);
        $modelConfig = new ModelConfig(); // mixed型を許可しない
        $fieldConfig = new FieldConfig(); // mixed型を許可しない

        $validator = new MixedTypeValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $this->expectException(InvalidPropertyStateException::class);
        $validator->validate($value);
    }

    /**
     * プロパティメタデータインスタンスを作成
     *
     * @param array<int, TypeHint> $typeHints
     */
    private function createPropertyMetadata(array $typeHints = []): PropertyMetadata
    {
        // PropertyMetadataの正しいコンストラクタ引数順序で初期化
        return new PropertyMetadata(
            'TestClass',
            'testProperty',
            $typeHints,
            PropertyInitializedStatus::BY_DEFAULT,
        );
    }
}
