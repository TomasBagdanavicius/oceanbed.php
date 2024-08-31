<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

use LWP\Common\Pager;

interface DataServerInterface
{
    // Returns the pager interface.

    public function getPager(): ?Pager;
}
