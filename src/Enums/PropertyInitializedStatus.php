<?php

declare(strict_types=1);

namespace PhSculptis\Enums;

enum PropertyInitializedStatus
{
    case UNINITIALIZED;
    case BY_DEFAULT;
    case BY_INPUT;
    case BY_FACTORY;
}
