<?php

declare(strict_types=1);

namespace LWP\Filesystem\FileType\FileFormats;

use LWP\Filesystem\FileType\File;
use LWP\Filesystem\Path\FilePath;

class FileCsv extends File
{
    public function __construct(
        FilePath $file_path,
        string $mode = 'a+',
        string $separator = ',',
        string $enclosure = '"',
        string $escape = '\\',
    ) {

        parent::__construct($file_path, $mode);

        $this->setFlags(\SplFileObject::READ_CSV);
        $this->setCsvControl($separator, $enclosure, $escape);
    }
}
