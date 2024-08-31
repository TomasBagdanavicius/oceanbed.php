<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

interface ColumnDataIteratorInterface
{
    //

    public function getColumnList(): array;


    //

    public function getDefaultColumnData(): array;

}
