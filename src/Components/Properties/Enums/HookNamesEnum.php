<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Enums;

enum HookNamesEnum
{
    case BEFORE_SET_VALUE;
    case AFTER_SET_VALUE;
    case BEFORE_GET_VALUE;
    case AFTER_GET_VALUE;
    case UNSET_VALUE;

}
