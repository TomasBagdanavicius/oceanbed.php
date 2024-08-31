<?php

declare(strict_types=1);

namespace LWP\Filesystem\Iterators;

class CustomDataImportIterator extends \IteratorIterator
{
    //

    public function current(): mixed
    {

        $current_element = parent::current();
        $custom_data = $this->getInnerIterator()->getInnerIterator()->current()->custom_data;

        return [...$current_element, ...$custom_data];
    }
}
