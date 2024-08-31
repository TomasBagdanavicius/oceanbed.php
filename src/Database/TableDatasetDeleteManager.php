<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\AbstractDatasetDeleteManager;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;

class TableDatasetDeleteManager extends AbstractDatasetDeleteManager
{
    public function __construct(
        TableDatasetStoreHandle $store_handle,
        protected ?DatasetManagementProcessInterface $process = null
    ) {

        parent::__construct($store_handle, $process);
    }


    //

    public function getDeleteEntryHandlerClassName(): string
    {

        return TableDatasetDeleteEntryHandler::class;
    }
}
