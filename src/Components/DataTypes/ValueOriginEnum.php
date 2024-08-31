<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes;

enum ValueOriginEnum
{
    case MAIN;
    case INTERNAL;
    case DEFAULT;
    case PARKED;

}
