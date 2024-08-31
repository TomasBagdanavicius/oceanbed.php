<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\AbstractDatasetStoreHandle;

class FilesystemDirectoryDatasetStoreHandle extends AbstractDatasetStoreHandle
{
    public function __construct(
        FilesystemDirectoryDataset $dataset,
        array $identifiers
    ) {

        parent::__construct($dataset, $identifiers);
    }


    //

    public function getCreateManagerClassName(): string
    {

        return FilesystemDirectoryDatasetCreateManager::class;
    }


    //

    public function getUpdateManagerClassName(): string
    {

        return FilesystemDirectoryDatasetUpdateManager::class;
    }


    //

    public function getDeleteManagerClassName(): string
    {

        return FilesystemDirectoryDatasetDeleteManager::class;
    }


    //

    public function getDatasetStoreManagementProcessClassName(): string
    {

        return FilesystemDirectoryDatasetStoreManagementProcess::class;
    }


    //

    public function getStoreFieldValueFormatterClassName(): string
    {

        return FilesystemDirectoryDatasetStoreFieldValueFormatter::class;
    }
}
