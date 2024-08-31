<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Iterators;

use LWP\Components\Datasets\Interfaces\DatabaseStoreFieldValueFormatterInterface;
use LWP\Components\Datasets\SpecialContainerCollection;

class AbstractStoreFieldValueFormatterIterator extends \IteratorIterator
{
    public function __construct(
        \Traversable $iterator,
        public readonly DatabaseStoreFieldValueFormatterInterface $formatter,
        public readonly SpecialContainerCollection $containers
    ) {

        parent::__construct($iterator);
    }


    //

    public function current(): mixed
    {

        return $this->formatter->formatByDataType(
            parent::current(),
            $this->containers->getDataTypeForContainer(parent::key())
        );
    }
}
