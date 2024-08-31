<?php

/* This class will not store longest or shortest string contents or key number,
because, simply put, logically there can be multiple lines of the same length,
meaning that a caching variant of this class should be used to get multiple
contents and key entries. */

namespace LWP\Common\Iterators;

class StringLengthStatIterator extends \IteratorIterator implements AccumulativeIteratorInterface
{
    protected ?int $longest_str_len = null;
    protected ?int $shortest_str_len = null;


    // Intercepts the current element and compares longest and shortest string values.

    public function current(): mixed
    {

        $current_element = parent::current();

        if (is_string($current_element)) {

            $string_length = strlen($current_element);

            if (!$this->longest_str_len || $string_length > $this->longest_str_len) {
                $this->longest_str_len = $string_length;
            }

            if (!$this->shortest_str_len || $string_length < $this->shortest_str_len) {
                $this->shortest_str_len = $string_length;
            }
        }

        return $current_element;
    }


    // Gets accumulated storage data.

    public function getStorage(): array|object
    {

        return [
            $this->longest_str_len,
            $this->shortest_str_len,
        ];
    }


    // Gets storage iterator.

    public function getStorageIterator(): \Traversable
    {

        return new \ArrayIterator($this->getStorage());
    }
}
