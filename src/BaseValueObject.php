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
use stdClass;

abstract class BaseValueObject
{
    /**
     * @param array<string, mixed> $args
     * 
     * @throws InheritableClassException|UninitializedException|ValidationException|TypeError
     */
    final protected function __construct(...$args)
    {
        $refClass = new ReflectionClass($this);

        $strict = new Strict($refClass);

        $assertion = new Assertion($refClass, $strict);

        // 入力値を取得
        $inputArguments = new InputArguments($refClass, $args);

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
        }
    }

    /**
     * 連想配列からValueObjectを作成する
     * 
     * @param array<string, mixed> $array
     * @return static
     */
    final public static function fromArray(array $array = []): static
    {
        return new static(...$array);
    }

    /**
     * オブジェクトからValueObjectを作成する
     * 
     * @param object $object
     * @return static
     */
    final public static function fromObject(object $object = new stdClass()): static
    {
        return self::fromArray(get_object_vars($object));
    }
}
