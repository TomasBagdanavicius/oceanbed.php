<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class CallbackIterator extends \IteratorIterator
{
    public function __construct(
        \Traversable $iterator,
        private \Closure $callback,
        ?string $class = null,
    ) {

        parent::__construct($iterator, $class);
    }


    // Intercepts the current element and runs is through the callback function.

    public function current(): mixed
    {

        return ($this->callback)(parent::current());
    }
}
