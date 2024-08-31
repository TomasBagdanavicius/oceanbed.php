<?php

declare(strict_types=1);

namespace LWP\Common\Iterators;

use LWP\Filesystem\Path\FilePath;
use LWP\Filesystem\FileType\File;

class TextFileCreateIterator extends \IteratorIterator
{
    /* Whether the result that gets passed onto the next iterator should be
    reduced to the result string that was written to the text file. */
    public const REDUCE_ARRAY = 1;

    protected File $file;


    public function __construct(
        \Traversable $iterator,
        FilePath $file_path,
        public readonly ?string $divider = null,
        public readonly ?int $flags = self::REDUCE_ARRAY,
        ?string $class = null,
    ) {

        parent::__construct($iterator, $class);

        $this->file = new File($file_path, 'w');
    }


    // Intercepts current element and writes data into the file.

    public function current(): mixed
    {

        $data = $original = parent::current();

        if (is_array($data)) {

            $data = (is_string($this->divider))
                ? implode($this->divider, $data)
                : $data[array_key_first($data)];

        } elseif (!is_string($data)) {

            throw new \TypeError(sprintf(
                "Data provided for text file creation must be of type string or array, \"%s\" given.",
                gettype($data)
            ));
        }

        $this->file->putLine($data);

        return (($this->flags & self::REDUCE_ARRAY))
            ? $data
            : $original;
    }
}
