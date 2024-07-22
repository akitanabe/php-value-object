<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject;

use ReflectionClass;
use TypeError;
use Akitanabe\PhpValueObject\Exceptions\InheritableClassException;
use Akitanabe\PhpValueObject\Exceptions\UninitializedException;
use Akitanabe\PhpValueObject\Exceptions\ValidationException;
use Akitanabe\PhpValueObject\Options\Strict;
use Akitanabe\PhpValueObject\Support\Assertion;
use Akitanabe\PhpValueObject\Support\InputArguments;
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

        $assertion = new Assertion($refClass, $strict);

        // 入力値を取得
        $inputArguments =  new InputArguments($refClass, $args);

        $propHelper = new PropertyHelper(
            $this,
            $refClass,
            $assertion,
            $strict,
            $inputArguments
        );
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
