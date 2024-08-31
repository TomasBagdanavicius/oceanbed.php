<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\TraversableExtender;
use LWP\Components\Datasets\Interfaces\DatasetResultInterface;

class DatasetResult extends TraversableExtender implements DatasetResultInterface
{
    public function __construct(
        \Traversable $iterator
    ) {

        parent::__construct($iterator);
    }
}
