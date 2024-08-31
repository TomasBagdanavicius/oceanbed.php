<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\Iterators\AbstractStoreFieldValueFormatterIterator;

class ArrayCollectionDatasetStoreFieldValueFormatterIterator extends AbstractStoreFieldValueFormatterIterator
{
    public function __construct(
        \Traversable $iterator,
        ArrayCollectionDatasetStoreFieldValueFormatter $formatter,
    ) {

        parent::__construct($iterator, $formatter);
    }
}
