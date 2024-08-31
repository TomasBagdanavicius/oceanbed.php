<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\Iterators\AbstractStoreFieldValueFormatterIterator;
use LWP\Components\Datasets\SpecialContainerCollection;

class TableStoreFieldValueFormatterIterator extends AbstractStoreFieldValueFormatterIterator
{
    public function __construct(
        \Traversable $iterator,
        DatabaseStoreFieldValueFormatter $formatter,
        SpecialContainerCollection $containers
    ) {

        parent::__construct($iterator, $formatter, $containers);
    }
}
