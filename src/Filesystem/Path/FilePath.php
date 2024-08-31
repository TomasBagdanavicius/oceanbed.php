<?php

declare(strict_types=1);

namespace LWP\Filesystem\Path;

use LWP\Filesystem\Filesystem;

class FilePath extends SearchPath
{
    public function __construct(
        string $path,
        array $separators,
        string $default_separator,
        int $segments_handling_mode = self::NO_EMPTY_SEGMENTS
    ) {

        parent::__construct($path, $separators, $default_separator, $segments_handling_mode);
    }


    // Checks whether this path exists in the system as a file.

    public function existsInFilesystem(): bool
    {

        return file_exists($this->__toString());
    }


    // Gets the standard file pointer resource representation of this file path.
    // @return (resource) - file pointer resource.

    public function openAsFilePointerResource(string $mode = 'r')
    {

        return Filesystem::filePointerOpen($this->__toString(), $mode);
    }


    //

    public function fromSelf(string $path): self
    {

        return new self($path, $this->getSeparators(), $this->getDefaultSeparator(), $this->getSegmentsHandlingMode());
    }
}
