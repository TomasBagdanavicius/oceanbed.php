<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Datasets\AbstractDatasetCreateEntryHandler;
use LWP\Database\TableDatasetStoreManagementProcess;

class TableDatasetCreateEntryHandler extends AbstractDatasetCreateEntryHandler
{
    public function __construct(
        array $data,
        TableDatasetCreateManager $create_manager,
        TableDatasetStoreManagementProcess $process = null
    ) {

        parent::__construct($data, $create_manager, $process);
    }
}
