<?php

declare(strict_types=1);

namespace PhSculptis\Test\Helpers;

use PhSculptis\Config\ModelConfig;
use PhSculptis\Exceptions\InheritableClassException;
use PhSculptis\Helpers\AssertionHelper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use PhSculptis\BaseModel;

// テスト用のクラス定義
final class FinalClass extends BaseModel {}
class InheritableClass extends BaseModel {}

/**
 * @internal
 */
class AssertionHelperTest extends TestCase
{
    /**
     * @return array<string, array{model:bool,field:bool}>
     */
    public static function configAllowPatternDataProvider(): array
    {
        return [
            'ModelConfig(true),FieldConfig(true)' => [
                'model' => true,
                'field' => true,
            ],
            'ModelConfig(true),FieldConfig(false)' => [
                'model' => true,
                'field' => false,
            ],
            'ModelConfig(false),FieldConfig(true)' => [
                'model' => true,
                'field' => false,
            ],
        ];
    }

    /**
     * クラスがfinalでなく、継承が許可されていない場合に例外をスローすることをテスト
     */
    #[Test]
    public function assertInheritableClassThrowsExceptionWhenNotFinalAndInheritanceNotAllowed(): void
    {
        $this->expectException(InheritableClassException::class);

        $refClass = new ReflectionClass(InheritableClass::class);
        $modelConfig = new ModelConfig(allowInheritableClass: false);

        AssertionHelper::assertInheritableClass($refClass, $modelConfig);
    }

    /**
     * クラスがfinalの場合、継承可否の設定に関係なく例外をスローしないことをテスト
     */
    #[Test]
    public function assertInheritableClassDoesNotThrowExceptionWhenFinal(): void
    {
        $refClass = new ReflectionClass(FinalClass::class);
        $modelConfig = new ModelConfig(allowInheritableClass: false);

        AssertionHelper::assertInheritableClass($refClass, $modelConfig);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true); // アサーションが必要なため
    }

    /**
     * 継承が許可されている場合、finalでないクラスでも例外をスローしないことをテスト
     */
    #[Test]
    public function assertInheritableClassDoesNotThrowExceptionWhenInheritanceAllowed(): void
    {
        $refClass = new ReflectionClass(InheritableClass::class);
        $modelConfig = new ModelConfig(allowInheritableClass: true);

        AssertionHelper::assertInheritableClass($refClass, $modelConfig);
        // @phpstan-ignore method.alreadyNarrowedType
        $this->assertTrue(true); // アサーションが必要なため
    }
}
