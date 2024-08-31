<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType\FileFormats;

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\FilePath;

class FileJson extends File
{
    public function __construct(
        FilePath $file_path,
        string $mode = 'a+',
    ) {

        parent::__construct($file_path, $mode);
    }
}
