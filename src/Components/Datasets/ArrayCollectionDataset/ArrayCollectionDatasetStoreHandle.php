<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\AbstractDatasetStoreHandle;

class ArrayCollectionDatasetStoreHandle extends AbstractDatasetStoreHandle
{
    public function __construct(
        ArrayCollectionDataset $dataset,
        array $identifiers
    ) {

        parent::__construct($dataset, $identifiers);
    }


    //

    public function getCreateManagerClassName(): string
    {

        return ArrayCollectionDatasetCreateManager::class;
    }


    //

    public function getUpdateManagerClassName(): string
    {

        return ArrayCollectionDatasetUpdateManager::class;
    }


    //

    public function getDeleteManagerClassName(): string
    {

        return ArrayCollectionDatasetDeleteManager::class;
    }


    //

    public function getDatasetStoreManagementProcessClassName(): string
    {

        return ArrayCollectionDatasetStoreManagementProcess::class;
    }


    //

    public function getStoreFieldValueFormatterClassName(): string
    {

        return ArrayCollectionDatasetStoreFieldValueFormatter::class;
    }
}
