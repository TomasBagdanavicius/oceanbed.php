<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

class EnhancedRecursiveIteratorIterator extends \RecursiveIteratorIterator
{
    public function __construct(
        \Traversable $iterator,
        int $mode = \RecursiveIteratorIterator::LEAVES_ONLY,
        int $flags = 0
    ) {

        parent::__construct($iterator, $mode, $flags);
    }


    //

    public function isRootGroup(): bool
    {

        return ($this->getDepth() === 0);
    }


    //

    public function isFirstInGroup(): bool
    {

        return !parent::key();
    }


    //

    public function isLastInGroup(): bool
    {

        return $this->getInnerIterator()->hasEnded();
    }


    //

    public function getPositionInGroup(): int
    {

        // One-based position number.
        return (parent::key() + 1);
    }
}
