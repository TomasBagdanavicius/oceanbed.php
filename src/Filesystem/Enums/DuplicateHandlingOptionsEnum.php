<?php

declare(strict_types=1);

namespace LWP\Filesystem\Enums;

enum DuplicateHandlingOptionsEnum
{
    case KEEP_BOTH;
    case REPLACE;
    case ABORT;

}
