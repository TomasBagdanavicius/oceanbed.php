<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\Iterators\AbstractStoreFieldValueFormatterIterator;
use LWP\Components\Datasets\SpecialContainerCollection;

class FilesystemDirectoryDatasetStoreFieldValueFormatterIterator extends AbstractStoreFieldValueFormatterIterator
{
    public function __construct(
        \Traversable $iterator,
        FilesystemDatabaseStoreFieldValueFormatter $formatter,
        SpecialContainerCollection $containers
    ) {

        parent::__construct($iterator, $formatter, $containers);
    }
}
