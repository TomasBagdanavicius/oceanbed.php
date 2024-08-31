<?php

declare(strict_types=1);

namespace LWP\Filesystem\Enums;

enum FileTypeEnum
{
    case FILE;
    case DIRECTORY;
    case LINK;

}
