<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Validators;

use ArrayIterator;
use PhpValueObject\Enums\ValidatorMode;
use PhpValueObject\Validators\ValidatorFunctionWrapHandler;
use PhpValueObject\Validators\Validatorable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Mockery;

class ValidatorFunctionWrapHandlerTest extends TestCase
{
    /**
     * PLAINモードのvalidatorが設定されている場合、他のvalidatorが実行されないことを確認
     */
    #[Test]
    public function testPlainValidatorPreventsOtherValidators(): void
    {
        // PLAINモードのvalidatorをモック作成
        $plainValidator = Mockery::mock(Validatorable::class);
        $plainValidator->shouldReceive('getMode')
            ->once()
            ->andReturn(ValidatorMode::PLAIN);
        $plainValidator->shouldReceive('validate')
            ->once()
            ->with('test')
            ->andReturn('plain validated');

        // その他のvalidatorをモック作成
        $otherValidator = Mockery::mock(Validatorable::class);
        // このvalidatorは呼ばれないはず
        $otherValidator->shouldNotReceive('validate');
        $otherValidator->shouldNotReceive('getMode');

        $validators = new ArrayIterator([$plainValidator, $otherValidator]);

        // @phpstan-ignore argument.type
        $handler = new ValidatorFunctionWrapHandler($validators);

        $result = $handler('test');

        $this->assertEquals('plain validated', $result);
    }

    /**
     * PLAINモードのvalidatorが設定されていない場合、他のvalidatorが通常通り実行されることを確認
     */
    #[Test]
    public function testNormalValidatorsExecuteWhenNoPlainValidator(): void
    {
        $validator1 = Mockery::mock(Validatorable::class);
        $validator1->shouldReceive('getMode')
            ->once()
            ->andReturn(ValidatorMode::BEFORE);
        $validator1->shouldReceive('validate')
            ->once()
            ->with('test')
            ->andReturn('first validated');

        $validator2 = Mockery::mock(Validatorable::class);
        $validator2->shouldReceive('getMode')
            ->once()
            ->andReturn(ValidatorMode::AFTER);
        $validator2->shouldReceive('validate')
            ->once()
            ->with('first validated')
            ->andReturn('second validated');

        $validators = new ArrayIterator([$validator1, $validator2]);

        // @phpstan-ignore argument.type
        $handler = new ValidatorFunctionWrapHandler($validators);

        $result = $handler('test');

        $this->assertEquals('second validated', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
