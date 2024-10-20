<?php

declare(strict_types=1);

namespace LWP\Common\Enums;

enum AccessModeEnum
{
    case READ;
    case WRITE;
    case READWRITE;
}
