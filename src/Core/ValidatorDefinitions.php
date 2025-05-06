<?php

declare(strict_types=1);

namespace PhpValueObject\Core;

use LogicException;

/**
 * バリデータのコンストラクタ引数をキャッシュするためのクラス
 *
 * 型をキーとしてバリデータのオブジェクトを保存・取得する
 */
class ValidatorDefinitions
{
    /**
     * バリデータオブジェクトのレジストリ
     * @var array<class-string, object>
     */
    private array $definitions = [];

    /**
     * バリデータオブジェクトを登録する
     *
     * @param object $validatorArgumentInstance バリデータの引数オブジェクト
     * @return self
     */
    public function register(object $validatorArgumentInstance): self
    {
        $this->definitions[$validatorArgumentInstance::class] = $validatorArgumentInstance;
        return $this;
    }

    /**
     * 複数のバリデータオブジェクトを一括で登録する
     *
     * @param object ...$validatorArgumentInstances バリデータの引数オブジェクト（可変長引数）
     * @return self
     */
    public function registerMultiple(object ...$validatorArgumentInstances): self
    {
        foreach ($validatorArgumentInstances as $instance) {
            $this->register($instance);
        }
        
        return $this;
    }

    /**
     * バリデータオブジェクトが登録されているか確認する
     *
     * @param class-string $type バリデータの型
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->definitions[$type]);
    }

    /**
     * バリデータの引数オブジェクトを取得する
     *
     * @template T of object
     * @param class-string<T> $type バリデータ引数の型
     * @return T|null 登録されていない場合はnull
     * @throws LogicException 登録されている型と異なる場合
     */
    public function get(string $type): ?object
    {
        $definition = $this->definitions[$type] ?? null;

        if ($definition === null) {
            return null;
        }

        if ($definition instanceof $type === false) {
            throw new LogicException(sprintf('Invalid type: %s', $type));
        }

        return $definition;
    }
}
