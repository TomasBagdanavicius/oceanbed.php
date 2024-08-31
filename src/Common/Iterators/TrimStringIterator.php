<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class TrimStringIterator extends \IteratorIterator
{
    // Trims and gets the current value.

    public function current(): mixed
    {

        $current_element = parent::current();

        // Filtering out string values instead of throwing an exception.
        return (is_string($current_element))
            ? trim($current_element)
            : $current_element;
    }
}
