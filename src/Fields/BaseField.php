<?php

declare(strict_types=1);

namespace PhpValueObject\Fields;

use Closure;
use PhpValueObject\Helpers\FieldsHelper;
use PhpValueObject\Core\Validators\Validatorable;

/**
 * フィールドの基底クラス
 * @phpstan-type default_factory callable-string|class-string|array{string|object, string}|Closure
 */
abstract class BaseField
{
    /**
     *
     * @param default_factory|null $defaultFactory default値の生成関数。default値が存在していたらUnexpectedValueExceptionが投げられます
     * @param string|null $alias フィールド名のエイリアス。指定された場合、エイリアス名で入力値を取得します
     */
    public function __construct(
        private readonly string|array|Closure|null $defaultFactory = null,
        public readonly ?string $alias = null,
    ) {
    }

    /**
     * @param array<string|int, mixed> $data
     *
     * @return mixed
     */
    public function defaultFactory(array $data): mixed
    {
        if ($this->defaultFactory === null) {
            return null;
        }

        $factoryCallable = FieldsHelper::createFactory($this->defaultFactory);

        return $factoryCallable($data);
    }

    public function hasDefaultFactory(): bool
    {
        return $this->defaultFactory !== null;
    }

    /**
     * バリデーターのクラス名を取得する
     * 各フィールドタイプに対応したValidatorableを実装したValidatorのクラス名を返す
     *
     * @return class-string<Validatorable> Validatorableを実装したValidatorのクラス名
     */
    abstract public function getValidator(): string;

    /**
     * バリデーター定義を取得する
     * 各フィールドタイプに対応したバリデーター定義オブジェクトを返す
     * definitionプロパティが存在しない場合はNoneDefinitionクラスを返す
     *
     * @return object バリデーター定義オブジェクト
     */
    abstract public function getDefinition(): object;
}
