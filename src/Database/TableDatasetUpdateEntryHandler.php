<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Datasets\AbstractDatasetUpdateEntryHandler;

class TableDatasetUpdateEntryHandler extends AbstractDatasetUpdateEntryHandler
{
    public function __construct(
        array $data,
        RelationalPropertyModel $model,
        TableDatasetUpdateManager $manager,
        // Data that must be set through private channel
        array $private_data = [],
        ?TableDatasetStoreManagementProcess $process = null
    ) {

        parent::__construct($data, $model, $manager, $private_data, $process);
    }
}
