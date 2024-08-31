<?php

declare(strict_types=1);

namespace LWP\Filesystem\Enums;

enum FileActionEnum
{
    case CREATE;
    case RENAME;
    case DELETE;
    case DUPLICATE;
    case TRUNCATE;
    case COPY;
    case COPY_TO;
    case MOVE;
    case MOVE_TO;
    case CUT;
    case COMPRESS;
    case ZIP;
    case UNZIP;

}
