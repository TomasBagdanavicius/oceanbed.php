<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Common\Indexable;

class AccumulativeIndexableIterator extends AccumulativeIterator
{
    public function current(): mixed
    {

        $current_element = \IteratorIterator::current();

        if ($current_element instanceof Indexable) {

            $indexable_data = $current_element->getIndexableData();
            $this->storage[] = $indexable_data;
        }

        return $current_element;
    }
}
