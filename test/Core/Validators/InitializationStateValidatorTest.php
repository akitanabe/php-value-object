<?php

declare(strict_types=1);

namespace PhSculptis\Test\Validators;

use PhSculptis\Config\FieldConfig;
use PhSculptis\Config\ModelConfig;
use PhSculptis\Core\Validators\InitializationStateValidator;
use PhSculptis\Core\Validators\ValidatorBuildTrait;
use PhSculptis\Enums\PropertyInitializedStatus;
use PhSculptis\Exceptions\InvalidPropertyStateException;
use PhSculptis\Support\PropertyMetadata;
use PhSculptis\Validators\ValidatorFunctionWrapHandler;
use PhSculptis\Validators\ValidatorQueue;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PhSculptis\Core\ValidatorDefinitions;
use PhSculptis\Core\Validators\Validatorable;

class InitializationStateValidatorTest extends TestCase
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

        $validator = new InitializationStateValidator($modelConfig, $fieldConfig, $metadata);
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

        $validator = new InitializationStateValidator($modelConfig, $fieldConfig, $metadata);
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

        $validator = new InitializationStateValidator($modelConfig, $fieldConfig, $metadata);
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

        $validator = new InitializationStateValidator($modelConfig, $fieldConfig, $metadata);
        $value = 'test_value';

        $this->expectException(InvalidPropertyStateException::class);
        $validator->validate($value);
    }

    /**
     * 未初期化プロパティが許可されている場合（モデルレベル）、後続のハンドラーが実行されないことを確認
     */
    #[Test]
    public function testValidateDoesNotCallNextHandlerForUninitializedPropertyAllowedByModel(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyInitializedStatus::UNINITIALIZED);
        $modelConfig = new ModelConfig(true); // 未初期化プロパティを許可
        $fieldConfig = new FieldConfig(false);

        $validator = InitializationStateValidator::class;
        $value = 'test_value';

        // 値を変更する実際のバリデータを作成
        $changedValue = new Value('changed_value');
        $nextValidator = ValueChangingValidator::class;

        // ValidatorQueueを直接作成
        $validators = new ValidatorQueue([$validator, $nextValidator]);
        $definitions = (new ValidatorDefinitions())->registerMultiple(
            $changedValue,
            $metadata,
            $modelConfig,
            $fieldConfig,
        );
        $handler = new ValidatorFunctionWrapHandler($validators, $definitions);

        $result = $handler($value);
        // 後続のハンドラーが実行されていなければ、値は変更されないままのはず
        $this->assertEquals($value, $result);
    }

    /**
     * 未初期化プロパティが許可されている場合（フィールドレベル）、後続のハンドラーが実行されないことを確認
     */
    #[Test]
    public function testValidateDoesNotCallNextHandlerForUninitializedPropertyAllowedByField(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyInitializedStatus::UNINITIALIZED);
        $modelConfig = new ModelConfig(false);
        $fieldConfig = new FieldConfig(true); // 未初期化プロパティを許可

        $validator = InitializationStateValidator::class;
        $value = 'test_value';

        // 値を変更する実際のバリデータを作成
        $nextValidator = ValueChangingValidator::class;
        $changedValue = new Value('changed_value');

        // ValidatorQueueを直接作成
        $validators = new ValidatorQueue([$validator, $nextValidator]);
        $definitions = (new ValidatorDefinitions())->registerMultiple(
            $changedValue,
            $metadata,
            $modelConfig,
            $fieldConfig,
        );
        $handler = new ValidatorFunctionWrapHandler($validators, $definitions);

        $result = $handler($value);
        // 後続のハンドラーが実行されていなければ、値は変更されないままのはず
        $this->assertEquals($value, $result);
    }

    /**
     * 初期化済みプロパティの場合、後続のハンドラーが実行されることを確認
     */
    #[Test]
    public function testValidateCallsNextHandlerForInitializedProperty(): void
    {
        $metadata = $this->createPropertyMetadata(PropertyInitializedStatus::BY_DEFAULT);
        $modelConfig = new ModelConfig(false);
        $fieldConfig = new FieldConfig(false);

        $validator = InitializationStateValidator::class;
        $value = 'test_value';

        // 値を変更する実際のバリデータを作成
        // 値を変更する実際のバリデータを作成
        $nextValidator = ValueChangingValidator::class;
        $changedValue = new Value('changed_value');

        // ValidatorQueueを直接作成
        $validators = new ValidatorQueue([$validator, $nextValidator]);
        $definitions = (new ValidatorDefinitions())->registerMultiple(
            $changedValue,
            $metadata,
            $modelConfig,
            $fieldConfig,
        );
        $handler = new ValidatorFunctionWrapHandler($validators, $definitions);

        $result = $handler($value);
        // 後続のハンドラーが実行されれば、値は変更されているはず
        $this->assertEquals($changedValue, $result);
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

class Value
{
    public function __construct(
        private string $value,
    ) {}

    public function __toString(): string
    {
        return $this->value;
    }
}

/**
 * テスト用の値を変更するバリデータ
 */
class ValueChangingValidator implements Validatorable
{
    use ValidatorBuildTrait;

    public function __construct(
        private Value $newValue,
    ) {}

    public function validate(mixed $value, ?ValidatorFunctionWrapHandler $handler = null): mixed
    {
        $changedValue = (string) $this->newValue;

        if ($handler !== null) {
            return $handler($changedValue);
        }

        return $changedValue;
    }
}
