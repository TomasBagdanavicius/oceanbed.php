<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\AbstractDatasetUpdateManager;

class FilesystemDirectoryDatasetUpdateManager extends AbstractDatasetUpdateManager
{
    public function __construct(
        FilesystemDirectoryDatasetStoreHandle $store_handle,
    ) {

        parent::__construct($store_handle);
    }


    //

    public function getUpdateEntryHandlerClassName(): string
    {

        return FilesystemDirectoryDatasetUpdateEntryHandler::class;
    }
}
