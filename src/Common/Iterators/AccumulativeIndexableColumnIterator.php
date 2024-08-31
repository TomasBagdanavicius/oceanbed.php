<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Common\Indexable;

class AccumulativeIndexableColumnIterator extends AccumulativeIterator
{
    public function current(): mixed
    {

        $current_element = \IteratorIterator::current();

        if ($current_element instanceof Indexable) {

            $indexable_data = $current_element->getIndexableData();

            foreach ($indexable_data as $key => $value) {

                if (!isset($this->storage[$key])) {
                    $this->storage[$key] = [$value];
                } else {
                    $this->storage[$key][] = $value;
                }
            }
        }

        return $current_element;
    }
}
