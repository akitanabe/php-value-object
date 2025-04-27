<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Core\Validators;

use PhpValueObject\Config\FieldConfig;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Enums\PropertyInitializedStatus;
use PhpValueObject\Enums\TypeHintType;
use PhpValueObject\Exceptions\InvalidPropertyStateException;
use PhpValueObject\Support\PropertyMetadata;
use PhpValueObject\Support\TypeHint;
use PhpValueObject\Core\Validators\NoneTypeValidator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class NoneTypeValidatorTest extends TestCase
{
    /**
     * 有効な型のプロパティの場合、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForValidTypeProperty(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::STRING, true, false)]);
        $modelConfig = new ModelConfig(); // none型を許可しない
        $fieldConfig = new FieldConfig(); // none型を許可しない

        $validator = new NoneTypeValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * None型が許可されている場合（モデルレベル）、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForNoneTypeAllowedByModel(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::NONE, false, false)]);
        $modelConfig = new ModelConfig(allowNoneTypeProperty: true); // none型を許可
        $fieldConfig = new FieldConfig(); // none型を許可しない

        $validator = new NoneTypeValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * None型が許可されている場合（フィールドレベル）、値をそのまま返すことを確認
     */
    #[Test]
    public function testValidateReturnsValueForNoneTypeAllowedByField(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::NONE, false, false)]);
        $modelConfig = new ModelConfig(); // none型を許可しない
        $fieldConfig = new FieldConfig(allowNoneTypeProperty: true); // none型を許可

        $validator = new NoneTypeValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $result = $validator->validate($value);
        $this->assertEquals($value, $result);
    }

    /**
     * None型が許可されていない場合、例外をスローすることを確認
     */
    #[Test]
    public function testValidateThrowsExceptionForNoneTypeNotAllowed(): void
    {
        $metadata = $this->createPropertyMetadata([new TypeHint(TypeHintType::NONE, false, false)]);
        $modelConfig = new ModelConfig(); // none型を許可しない
        $fieldConfig = new FieldConfig(); // none型を許可しない

        $validator = new NoneTypeValidator($modelConfig, $fieldConfig, $metadata);
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
