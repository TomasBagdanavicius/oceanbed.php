<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Common\Interfaces\Sizeable;

class AggregateSizeLimitIterator extends \IteratorIterator
{
    protected int $aggregate_size = 0;
    private array $visited_keys = [];


    public function __construct(
        \Traversable $iterator,
        public readonly int $max_aggregate_size,
        ?string $class = null
    ) {

        parent::__construct($iterator, $class);
    }


    //

    public function getAggregateSize(): int
    {

        return $this->aggregate_size;
    }


    //

    public function valid(): bool
    {

        $is_valid = parent::valid();

        if (!$is_valid) {
            return $is_valid;
        }

        $key = parent::key();

        /* Iterators wrapped around this iterator might call this function the
        same way it's calling its parent `parent::valid()`. To prevent repeating
        the procedure below I am using a cache of visited keys. */
        if (in_array($key, $this->visited_keys)) {
            return $is_valid;
        }

        $this->visited_keys[] = $key;
        $current_element = parent::current();

        if (!($current_element instanceof Sizeable)) {
            throw new \Exception("Element must implement Sizeable");
        }

        $element_size = $current_element->getSize();

        if ($element_size > $this->max_aggregate_size) {
            return false;
        }

        $this->aggregate_size += $element_size;

        if ($this->aggregate_size > $this->max_aggregate_size) {
            return false;
        }

        return $is_valid;
    }
}
