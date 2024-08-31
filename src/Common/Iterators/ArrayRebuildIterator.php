<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Common\Exceptions\ElementNotFoundException;

\LWP\Autoload::loadFileByNamespaceName('LWP\Common\Array\Arrays', false);
use function LWP\Common\Array\Arrays\fetchByPath;

class ArrayRebuildIterator extends \IteratorIterator
{
    public function __construct(
        \Traversable $iterator,
        private array $map,
        ?string $class = null,
    ) {

        parent::__construct($iterator, $class);
    }


    //

    public function current(): mixed
    {

        $current_element = parent::current();
        $result = [];

        foreach ($this->map as $key => $path) {

            try {
                $result[$key] = fetchByPath($current_element, $path);
            } catch (ElementNotFoundException) {
                // Do nothing. This exception is required to skip unresolved paths.
            }
        }

        return $result;
    }
}
