<?php

declare(strict_types=1);

namespace PhSculptis\Test;

use PhSculptis\BaseModel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BasicInputTestModel extends BaseModel
{
    public string $string;

    public bool $bool;

    public int $int;

    public float $float;
}

final class DefaultOverrideContructorModel extends BaseModel
{
    public string $string = 'string';

    public bool $bool = true;

    public int $int = 1;

    public float $float = 0.1;
}

class BaseModelInputTest extends TestCase
{
    // 基本入力テスト
    #[Test]
    public function basicInput(): void
    {
        $model = BasicInputTestModel::fromArray(
            [
                'string' => 'string',
                'bool' => true,
                'int' => 1,
                'float' => 0.1,
            ],
        );

        $this->assertSame('string', $model->string);
        $this->assertSame(true, $model->bool);
        $this->assertSame(1, $model->int);
        $this->assertSame(0.1, $model->float);
    }

    // デフォルト値をオーバーライドされた場合、されていない場合のテスト
    #[Test]
    public function defaultOverrideConstructor(): void
    {
        $model = DefaultOverrideContructorModel::fromArray([
            'string' => 'default',
            'bool' => false,
        ],);

        $this->assertSame('default', $model->string);
        $this->assertSame(false, $model->bool);
        $this->assertSame(1, $model->int);
        $this->assertSame(0.1, $model->float);
    }
}
