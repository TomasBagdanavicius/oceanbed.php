<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class AccumulativeIterator extends \IteratorIterator implements AccumulativeIteratorInterface
{
    /* At this moment not using collection model, because this can go through large iterators with a lot of data and it seems as if a simply array would be more efficient for now in comparison to collecting into a collection object. */
    protected array $storage = [];


    public function __construct(
        \Traversable $iterator,
        ?string $class = null
    ) {

        parent::__construct($iterator, $class);
    }


    // Intercepts the current element and stores the value into the container.

    public function current(): mixed
    {

        $current_element = parent::current();
        $this->storage[] = $current_element;

        return $current_element;
    }


    // Gets storage container.

    public function getStorage(): array|object
    {

        return $this->storage;
    }


    // Gets storage iterator.

    public function getStorageIterator(): \Traversable
    {

        return new \ArrayIterator($this->getStorage());
    }
}
