<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Relationships;

enum RelatedTypeEnum
{
    case OWN;
    case FOREIGN_READ;
    case FOREIGN_WRITE;

}
