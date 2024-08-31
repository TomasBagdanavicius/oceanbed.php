<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class EmptyStringFilterIterator extends \FilterIterator
{
    // Checks whether current element is a string and whether it's not empty.

    public function accept(): bool
    {

        $current_element = $this->current();

        // Checking for string condition instead of throwing an exception.
        return (is_string($current_element))
            ? ($current_element !== '')
            : parent::accept();
    }
}
