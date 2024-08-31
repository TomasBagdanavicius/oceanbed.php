<?php

declare(strict_types=1);

namespace LWP\Database\Iterators;

use LWP\Database\Result;

class ResultIterator implements \Iterator
{
    public const GET_ARRAY = 1;
    public const GET_OBJECT = 2;

    private array|object|null|false $data;
    private int $position = 0;


    public function __construct(
        private Result $result,
        private int $flag = self::GET_ARRAY
    ) {

    }


    // Return the current element.

    public function current(): mixed
    {

        return $this->data;
    }


    // Return the key of the current element.

    public function key(): int
    {

        return $this->position;
    }


    // Move forward to next element.

    public function next(): void
    {

        $this->position++;
    }


    // No effect, because this is a no rewind iterator.

    public function rewind(): void
    {

        // No rewind iterator.
    }


    // Checks if next row exists in the result.

    public function valid(): bool
    {

        [$func_name, $param] = Result::getFetchMethodParamsByFormat($this->flag);

        $this->data = $this->result->getMysqliResult()
            ->{$func_name}($param);

        return (is_array($this->data));
    }
}
