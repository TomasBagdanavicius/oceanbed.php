<?php

declare(strict_types=1);

namespace LWP\Filesystem\Enums;

enum FilesystemStatsStatusEnum
{
    case SUCCESS;
    case FAILURE;
    case FOUND;
    case NOT_FOUND;

}
