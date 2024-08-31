<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType\FileFormats;

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\FilePath;

class FilePhp extends File
{
    public function __construct(
        FilePath $file_path,
        ?FilesystemStats $filesystem_stats = null
    ) {

        parent::__construct($file_path, $filesystem_stats);
    }


    // Gets a list containing all PHP tokens.

    public function getTokenList(): array
    {

        return \PhpToken::tokenize(
            $this->getContents()
        );
    }


    // Gets the token iterator.

    public function getTokenIterator(): PhpTokenIterator
    {

        return new PhpTokenIterator(
            $this->getContents()
        );
    }


    // Removes trailing PHP's close tag.

    public function removeTrailingCloseTag(): ?bool
    {

        #tbd
    }
}
