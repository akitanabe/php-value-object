<?php

namespace PhSculptis\Helpers;

use PhSculptis\Config\ModelConfig;
use PhSculptis\Exceptions\InheritableClassException;
use ReflectionClass;
use PhSculptis\BaseModel;

class AssertionHelper
{
    /**
     * @param ReflectionClass<BaseModel> $refClass
     *
     * @throws InheritableClassException
     */
    public static function assertInheritableClass(ReflectionClass $refClass, ModelConfig $modelConfig): void
    {
        if (
            $refClass->isFinal() === false
            && $modelConfig->inheritableClass->disallow()
        ) {
            throw new InheritableClassException(
                "{$refClass->name} is not allowed to inherit. not allow inheritable class.",
            );
        }
    }
}
