<?php

declare(strict_types=1);

namespace LWP\Common;

interface Indexable
{
    //

    public function getIndexablePropertyList(): array;


    //

    public function getIndexableData(): array|object;


    //

    public function updateIndexableEntry(int|string $name, int|string $value): void;

}
