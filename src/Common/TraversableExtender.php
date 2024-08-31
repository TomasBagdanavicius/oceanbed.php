<?php

declare(strict_types=1);

namespace LWP\Common;

class TraversableExtender implements \Countable, \IteratorAggregate
{
    public function __construct(
        public readonly \Traversable $traversable
    ) {

    }


    //

    public function getIterator(): \Traversable
    {

        return $this->traversable;
    }


    //

    public function count(): int
    {

        return iterator_count($this->traversable);
    }


    //

    public function getFirst(): mixed
    {

        foreach ($this->traversable as $data) {
        }
        $this->traversable->rewind();

        return ($data ?? null);
    }


    //

    public function toArray(): array
    {

        return iterator_to_array($this->traversable);
    }
}
