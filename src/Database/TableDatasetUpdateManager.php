<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\AbstractDatasetUpdateManager;

class TableDatasetUpdateManager extends AbstractDatasetUpdateManager
{
    public function __construct(
        TableDatasetStoreHandle $store_handle
    ) {

        parent::__construct($store_handle);
    }


    //

    public function getUpdateEntryHandlerClassName(): string
    {

        return TableDatasetUpdateEntryHandler::class;
    }
}
