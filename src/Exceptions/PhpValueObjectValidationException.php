<?php

namespace Akitanabe\PhpValueObject\Exceptions;

use Exception;
use Akitanabe\PhpValueObject\Validation\Validatable;
use ReflectionProperty;

class PhpValueObjectValidationException extends Exception
{
    public function __construct(Validatable $validator, ReflectionProperty $refProp)
    {
        $className = $refProp->getDeclaringClass()->getName();
        $propName  = $refProp->getName();

        parent::__construct(
            "Validataion Error {$className}::\${$propName} "
                . $validator->errorMessage()
        );
    }
}
