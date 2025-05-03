<?php

declare(strict_types=1);

namespace PhpValueObject\Validators;

use Attribute;
use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use RuntimeException;
use InvalidArgumentException;

/**
 * 静的メソッドが対象とし、システムバリデータの実行前後にバリデーションを実行するAttribute
 * modeの指定で、バリデーションの実行タイミングを指定する
 * 
 * @phpstan-import-type validator_callable from ValidatorCallable
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_METHOD)]
final class FieldValidator implements ValidatorCallable
{
    /**
     * バリデーターモード
     */
    private readonly ValidatorMode $mode;

    /**
     * バリデーション処理を行うcallable
     *
     * @var validator_callable|null
     */
    private string|array|Closure|null $callable = null;

    /**
     * @param string $field バリデーション対象のフィールド名
     */
    public function __construct(
        public readonly string $field,
        ValidatorMode $mode = ValidatorMode::AFTER,
    ) {
        $this->mode = $mode;
    }

    /**
     * バリデーション処理を行う callable をセットする
     *
     * @param validator_callable $callable バリデーション処理を行う callable
     * @return self
     */
    public function setCallable(string|array|Closure $callable): self
    {
        $this->callable = $callable;
        return $this;
    }

    /**
     * バリデーション処理を行う Closure を返す
     *
     * @throws RuntimeException callable が設定されていない場合
     * @throws InvalidArgumentException callable が無効な場合
     */
    public function resolveValidator(): Closure
    {
        if ($this->callable === null) {
            throw new RuntimeException('Validator callable is not set');
        }
        return FieldsHelper::createFactory($this->callable);
    }

    /**
     * バリデーションのモードを取得する
     *
     * @return ValidatorMode バリデーションモード
     */
    public function getMode(): ValidatorMode
    {
        return $this->mode;
    }
}
