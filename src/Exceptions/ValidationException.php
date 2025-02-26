<?php

namespace PhpValueObject\Exceptions;

use PhpValueObject\Validation\Validatable;
use Exception;
use ReflectionProperty;

class ValidationException extends Exception
{
    public function __construct(Validatable $validator, ReflectionProperty $refProp)
    {
        $className = $refProp->getDeclaringClass()->getName();
        $propName = $refProp->getName();

        parent::__construct(
            "Validataion Error {$className}::\${$propName} "
            . $validator->errorMessage(),
        );
    }
}
