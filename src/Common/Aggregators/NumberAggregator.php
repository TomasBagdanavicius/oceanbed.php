<?php

declare(strict_types=1);

namespace LWP\Common\Aggregators;

use LWP\Common\Exceptions\AbortException;
use LWP\Common\Interfaces\Sizeable;

class NumberAggregator extends AbstractAggregator implements Sizeable
{
    public function __construct(
        private int|float $number = 0,
    ) {

    }


    // Increases compound value by the given number.

    public function set(int|float $number): void
    {

        $this->number += $number;

        // Successful set.
        parent::tick($number);
    }


    // Gets the compound value.

    public function getCompound(): int|float
    {

        return $this->number;
    }


    // An alias of the "getCompound" method (required by the "Sizeable" interface).

    public function getSize(): int|float
    {

        return $this->getCompound();
    }
}
