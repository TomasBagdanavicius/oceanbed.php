<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Enums;

enum DatasetActionStatusEnum: int
{
    case ERROR = 0;
    case SUCCESS = 1;
    case FOUND = 2;
    case EMPTY = 3;
    case DENIED = 4;

}
