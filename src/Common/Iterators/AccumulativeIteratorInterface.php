<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

interface AccumulativeIteratorInterface
{
    public function getStorage(): array|object|string;

    public function getStorageIterator(): \Traversable;

}
