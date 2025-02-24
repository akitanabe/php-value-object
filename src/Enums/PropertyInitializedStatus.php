<?php

declare(strict_types=1);

namespace Akitanabe\PhpValueObject\Enums;

enum PropertyInitializedStatus
{
    case UNINITIALIZED;
    case BY_DEFAULT;
    case INPUTED;
}