<?php

declare(strict_types=1);

namespace LWP\Network\String;

use LWP\Common\String\ChunkIterator;

class ChunkedIterator extends ChunkIterator
{
    public function __construct(
        string $data
    ) {

        parent::__construct($data);
    }


    // Gets a chunk element.

    public function get(int $size = 76): string
    {

        $data = parent::get($size);

        $result = self::buildChunk($size, $data);

        if (!$this->getRemainingSize()) {
            $result .= self::end();
        }

        return $result;
    }


    // Ends the entire chunked response.

    public static function end(): string
    {

        return '0';
    }


    // Builds a chunk string.

    public static function buildChunk(int $size, string $data): string
    {

        $result = dechex($size) . "\r\n";
        $result .= $data;
        $result .= "\r\n";

        return $result;
    }
}
