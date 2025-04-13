<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use ArrayIterator;
use PhpValueObject\Validators\Validatorable;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Enums\ValidatorMode;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;
use Mockery\MockInterface;

class ValidatorFunctionWrapHandlerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * バリデーターが存在しない場合、元の値がそのまま返されることを確認するテスト
     */
    #[Test]
    public function whenNoValidatorsReturnOriginalValue(): void
    {
        $validators = new ArrayIterator([]);
        $handler = new ValidatorFunctionWrapHandler($validators);

        $result = $handler('test');

        $this->assertSame('test', $result);
    }

    /**
     * 有効なモードを持つバリデーターが値を正しく処理することを確認するテスト
     */
    #[Test]
    public function validatorsProcessesValueWithValidMode(): void
    {
        /** @var MockInterface $validator1 */
        $validator1 = Mockery::mock(Validatorable::class);
        $validator1->shouldReceive('getMode')->andReturn(ValidatorMode::BEFORE);
        $validator1->shouldReceive('validate')
            ->with('test')
            ->andReturn('TEST');

        $validators = new ArrayIterator([$validator1]);

        // @phpstan-ignore argument.type (ValidatorableのMockオブジェクトが入力されている)
        $handler = new ValidatorFunctionWrapHandler($validators);

        $result = $handler('test');

        $this->assertSame('TEST', $result);
    }


    /**
     * 複数のバリデーターが順番に処理されることを確認するテスト
     */
    #[Test]
    public function multipleValidatorsProcessInSequence(): void
    {
        /** @var MockInterface $validator1 */
        $validator1 = Mockery::mock(Validatorable::class);
        $validator1->shouldReceive('getMode')->andReturn(ValidatorMode::BEFORE);
        $validator1->shouldReceive('validate')
            ->with('test')
            ->andReturn('TEST');

        /** @var MockInterface $validator2 */
        $validator2 = Mockery::mock(Validatorable::class);
        $validator2->shouldReceive('getMode')->andReturn(ValidatorMode::AFTER);
        $validator2->shouldReceive('validate')
            ->with('TEST')
            ->andReturn('TEST_MODIFIED');

        $validators = new ArrayIterator([$validator1, $validator2]);

        /** @phpstan-ignore argument.type (ValidatorableのMockオブジェクトが入力されている) */
        $handler = new ValidatorFunctionWrapHandler($validators);

        $result = $handler('test');

        $this->assertSame('TEST_MODIFIED', $result);
    }
}
