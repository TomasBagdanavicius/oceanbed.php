<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\AbstractDatasetCreateManager;
use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;

class TableDatasetCreateManager extends AbstractDatasetCreateManager
{
    public function __construct(
        TableDatasetStoreHandle $store_handle,
        protected ?DatasetManagementProcessInterface $process = null
    ) {

        parent::__construct($store_handle, $process);
    }


    //

    public function getCreateEntryHandlerClassName(): string
    {

        return TableDatasetCreateEntryHandler::class;
    }


    //

    public function manyFromArray(array $data, bool $commit = true): array
    {

        return $this->manyFromArrayMake($data, $commit, flatten: true);
    }
}
