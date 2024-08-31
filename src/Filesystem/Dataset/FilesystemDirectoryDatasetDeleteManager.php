<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\AbstractDatasetDeleteManager;

class FilesystemDirectoryDatasetDeleteManager extends AbstractDatasetDeleteManager
{
    public function __construct(
        FilesystemDirectoryDatasetStoreHandle $store_handle
    ) {

        parent::__construct($store_handle);
    }
}
