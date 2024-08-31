<?php

declare(strict_types=1);

namespace LWP\Common;

class Pager implements \Countable
{
    public function __construct(
        public readonly int $count,
        public readonly int $per_page,
        public readonly int $current_page = 1,
    ) {

        #todo: add full constrains for all integer values
    }


    //

    public function count(): int
    {

        return $this->getPageCount();
    }


    //

    public function getPageCount(): int
    {

        if ($this->count === 0) {
            return 0;
        }

        return intval(ceil($this->count / $this->per_page));
    }


    //

    public function hasMorePages(): bool
    {

        return $this->hasNextPage();
    }


    //

    public function hasNextPage(): bool
    {

        return ($this->current_page < $this->getPageCount());
    }


    //

    public function hasPreviousPage(): bool
    {

        return (($this->current_page - 1) > 0);
    }


    //

    public function getNextPageNumber(): ?int
    {

        if (!$this->hasNextPage()) {
            return null;
        }

        return ($this->current_page + 1);
    }


    //

    public function getPreviousPageNumber(): ?int
    {

        if (!$this->hasPreviousPage()) {
            return null;
        }

        return ($this->current_page - 1);
    }
}
