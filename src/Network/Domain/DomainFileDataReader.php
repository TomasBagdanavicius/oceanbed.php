<?php

declare(strict_types=1);

namespace LWP\Network\Domain;

use LWP\Filesystem\Path\FilePath;
use LWP\Filesystem\Files\PublicSuffixListFile;

class DomainFileDataReader extends DomainDataReader
{
    private array $data;


    public function __construct(
        public readonly FilePath $file_path,
    ) {

        $file = new PublicSuffixListFile($file_path);

        /* Stores the entire public suffix dataset, which is optimal for multiple queries,
        though somewhat an overkill, if only a single query is required. However, this
        assumes that most of the time, multiple queries will be made. */
        foreach ($file as $key => $data) {
            $this->data[$data['u_label']] = $data;
        }
    }


    // Tells if a given unicode label exists in the file database.

    public function containsEntry(string $unicode_label): bool
    {

        return isset($this->data[$unicode_label]);
    }
}
