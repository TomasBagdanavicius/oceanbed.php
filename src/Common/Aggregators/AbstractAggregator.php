<?php

declare(strict_types=1);

namespace LWP\Common\Aggregators;

abstract class AbstractAggregator
{
    private int $times_set_count = 0;
    private mixed $last_set_value = null;


    //

    abstract public function getCompound(): mixed;


    //

    final protected function tick(mixed $value): void
    {

        $this->times_set_count++;
        $this->last_set_value = $value;
    }


    //

    public function getTimesSetCount(): int
    {

        return $this->times_set_count;
    }


    //

    public function getLastSetValue(): mixed
    {

        return $this->last_set_value;
    }
}
