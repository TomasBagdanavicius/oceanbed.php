<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\Interfaces;

interface DatasetResultInterface
{
    //

    public function getIterator(): \Traversable;


    //

    public function count(): int;


    //

    public function getFirst(): mixed;


    //

    public function toArray(): array;
}
