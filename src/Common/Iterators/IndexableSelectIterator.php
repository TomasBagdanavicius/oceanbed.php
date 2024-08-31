<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Common\Indexable;

class IndexableSelectIterator extends \IteratorIterator
{
    public function __construct(
        \Traversable $iterator,
        protected array $property_list = []
    ) {

        parent::__construct($iterator);
    }


    //

    public function current(): mixed
    {

        $element = parent::current();

        if (!($element instanceof Indexable)) {
            throw new \InvalidArgumentException(sprintf(
                "Iterator element must be an instance of %s",
                Indexable::class
            ));
        }

        return $element->getIndexableData($this->property_list);
    }
}
