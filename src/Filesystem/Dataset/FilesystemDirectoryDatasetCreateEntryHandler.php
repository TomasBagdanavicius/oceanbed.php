<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\AbstractDatasetCreateEntryHandler;

class FilesystemDirectoryDatasetCreateEntryHandler extends AbstractDatasetCreateEntryHandler
{
    public function __construct(
        array $data,
        FilesystemDirectoryDatasetCreateManager $create_manager,
        $process = null,
    ) {

        parent::__construct($data, $create_manager, $process);
    }
}
