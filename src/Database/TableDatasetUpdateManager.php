<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\AbstractDatasetUpdateManager;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;

class TableDatasetUpdateManager extends AbstractDatasetUpdateManager
{
    public function __construct(
        TableDatasetStoreHandle $store_handle,
        protected ?DatasetManagementProcessInterface $process = null,
    ) {

        parent::__construct($store_handle, $process);
    }


    //

    public function getUpdateEntryHandlerClassName(): string
    {

        return TableDatasetUpdateEntryHandler::class;
    }
}
