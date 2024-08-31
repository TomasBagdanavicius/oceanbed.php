<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\Collections\Collection;

trait CollectableTrait
{
    private array $collections = [];


    //

    public function getCollections(): array
    {

        return $this->collections;
    }


    //

    public function registerCollection(Collection $collection, int|string $index_number): void
    {

        $this->collections[] = [$collection, $index_number];
    }
}
