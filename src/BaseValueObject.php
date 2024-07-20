<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use ReflectionClass;
use ReflectionAttribute;
use ReflectionProperty;
use TypeError;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Helpers\AssertHelper;
use Akitanabe\PhpValueObject\Helpers\ArgumentsHelper;
use Akitanabe\PhpValueObject\Helpers\PropertyHelper;


abstract class BaseValueObject
{
    /**
     * @param array<string|int,mixed> $args
     * 
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    public function __construct(...$args)
    {
        $refClass = new ReflectionClass($this);

        $strict = new Strict($refClass);

        // finalクラスであることを強制(Attributeが設定されていなければ継承不可)
        AssertHelper::assertInheritableClass($refClass, $strict);

        // 入力値を取得
        $inputArgs = ArgumentsHelper::getInputArgs($refClass, $args);

        $propHelper = new PropertyHelper($this, $refClass, $strict, $inputArgs);
        $propHelper->execute();
    }

    /**
     * クローン時にはimmutableにするためオブジェクトはcloneする
     */
    public function __clone()
    {
        foreach (get_object_vars($this) as $prop => $value) {
            if (is_object($value)) {
                $this->{$prop} = clone $value;
            }
        };
    }
}
