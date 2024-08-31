<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\AbstractDatasetStoreHandle;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;

class TableDatasetStoreHandle extends AbstractDatasetStoreHandle
{
    public function __construct(
        Table $dataset,
        array $identifiers
    ) {

        parent::__construct($dataset, $identifiers);
    }


    //

    public function getCreateManagerClassName(): string
    {

        return TableDatasetCreateManager::class;
    }


    //

    public function getUpdateManagerClassName(): string
    {

        return TableDatasetUpdateManager::class;
    }


    //

    public function getDeleteManagerClassName(): string
    {

        return TableDatasetDeleteManager::class;
    }


    //

    public function getDatasetStoreManagementProcessClassName(): string
    {

        return TableDatasetStoreManagementProcess::class;
    }


    //

    public function getStoreFieldValueFormatterClassName(): string
    {

        return TableStoreFieldValueFormatter::class;
    }
}
