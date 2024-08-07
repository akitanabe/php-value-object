<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Akitanabe\PhpValueObject\BaseValueObject;

final class BasicInputTestValue extends BaseValueObject
{
    public string $string;
    public bool $bool;
    public int $int;
    public float $float;
}

final class OverrideContructorValue extends BaseValueObject
{
    public string $string;
    public bool $bool;
    public int $int;
    public float $float;

    public function __construct(
        string $string,
        bool $bool,
        int $int,
        float $float,
    ) {
        parent::__construct(...func_get_args());
    }
}

final class DefaultOverrideContructorValue extends BaseValueObject
{
    public function __construct(
        public string $string = "string",
        public bool $bool = true,
        public int $int = 1,
        public float $float = 0.1
    ) {
        parent::__construct(...func_get_args());
    }
}

final class AppendOverrideContructorValue extends BaseValueObject
{
    public bool $bool;
    public int $int;
    public float $float;

    public function __construct(
        public string $string = "string"
    ) {
        parent::__construct(
            ...func_get_args(),
            ...["bool" => false, "int" => 1, "float" => 0.1]
        );
    }
}

final class ExtendOverrideContructorValue extends BaseValueObject
{
    public float $float;

    public function __construct(
        public string $string,
        public bool $bool = false,
        public int $int = 1,
    ) {
        parent::__construct(
            ...func_get_args(),
            ...["float" => 0.3]
        );
    }
}

class BaseValueObjectInputTest extends TestCase
{
    // 基本入力テスト
    #[Test]
    public function basicInput(): void
    {
        $value = new BasicInputTestValue(
            string: "string",
            bool: true,
            int: 1,
            float: 0.1,
        );

        $this->assertSame("string", $value->string);
        $this->assertSame(true, $value->bool);
        $this->assertSame(1, $value->int);
        $this->assertSame(0.1, $value->float);
    }

    /**
     * @return array<array{OverrideContructorValue}>
     */
    public static function overrideConstructorProvider(): array
    {
        return [
            [new OverrideContructorValue(
                string: "string",
                bool: true,
                int: 1,
                float: 0.1,
            )],
            [new OverrideContructorValue(
                "string",
                true,
                1,
                0.1,
            )],
        ];
    }

    /**
     * コンストラクタをオーバーライドされた場合のテスト
     * @param OverrideContructorValue $overrideValue
     * @return void
     */
    #[Test]
    #[DataProvider("overrideConstructorProvider")]
    public function overrideConstructor(
        OverrideContructorValue $overrideValue
    ): void {

        $this->assertSame("string", $overrideValue->string);
        $this->assertSame(true, $overrideValue->bool);
        $this->assertSame(1, $overrideValue->int);
        $this->assertSame(0.1, $overrideValue->float);
    }

    // デフォルト値をオーバーライドされた場合、されていない場合のテスト
    #[Test]
    public function defaultOverrideConstructor(): void
    {
        $value = new DefaultOverrideContructorValue(
            "default",
            bool: false,
        );

        $this->assertSame("default", $value->string);
        $this->assertSame(false, $value->bool);
        $this->assertSame(1, $value->int);
        $this->assertSame(0.1, $value->float);
    }

    // コンストラクタ内で値を追加した場合のテスト
    #[Test]
    public function appendOverrideConstructor(): void
    {
        $value = new AppendOverrideContructorValue(
            "append",
        );

        $this->assertSame("append", $value->string);
        $this->assertSame(false, $value->bool);
        $this->assertSame(1, $value->int);
        $this->assertSame(0.1, $value->float);
    }

    // 全部入りのテスト
    #[Test]
    public function extendOverrideConstructor(): void
    {
        $value = new ExtendOverrideContructorValue(
            "extend",
            int: 3,
        );

        $this->assertSame("extend", $value->string);
        $this->assertSame(false, $value->bool);
        $this->assertSame(3, $value->int);
        $this->assertSame(0.3, $value->float);
    }
}
