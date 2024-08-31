<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\Collections\Collection;

interface Collectable
{
    //

    public function getCollections(): array;


    //

    public function registerCollection(Collection $collection, int $index_number): void;

}
