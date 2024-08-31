<?php

declare(strict_types=1);

namespace LWP\Common\String;

class ChunkIterator implements \Iterator
{
    private int $data_length;
    private int $position;


    public function __construct(
        private string $data,
        private int $chunk_size = 76
    ) {

        $this->data_length = strlen($data);
        $this->rewind();
    }


    // Gets current chunk.

    public function current(int $chunk_size = 0): mixed
    {

        if (!$chunk_size) {
            $chunk_size = $this->chunk_size;
        }

        return substr($this->data, $this->position, $chunk_size);
    }


    // Gets the position key.

    public function key(): int
    {

        return $this->position;
    }


    // Iterates to the next position by given chunk size.

    public function next(int $chunk_size = 0): void
    {

        $this->position += (!$chunk_size)
            ? $this->chunk_size
            : $chunk_size;
    }


    // Rewinds the position to be read from the beginning.

    public function rewind(): void
    {

        $this->position = 0;
    }


    // Tells if there are further data to read.

    public function valid(): bool
    {

        return ($this->position < $this->data_length);
    }


    // Gets a single chunk.

    public function get(int $size = 76): string
    {

        $result = $this->current($size);
        $this->next($size);

        return $result;
    }


    // Gets the remaining size.

    public function getRemainingSize(): int
    {

        return max(0, ($this->data_length - $this->position));
    }
}
