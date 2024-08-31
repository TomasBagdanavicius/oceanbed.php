<?php

declare(strict_types=1);

namespace LWP\Common\Enums;

enum ExtendedOrderEnum: string
{
    case ASC = "Ascending";
    case DESC = "Descending";
    case REL = "Relevance";

}
