<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Datasets\AbstractDatasetUpdateEntryHandler;

class FilesystemDirectoryDatasetUpdateEntryHandler extends AbstractDatasetUpdateEntryHandler
{
    public function __construct(
        array $data,
        RelationalPropertyModel $model,
        FilesystemDirectoryDatasetUpdateManager $manager,
        // Data that must be set through private channel
        array $private_data = [],
        ?FilesystemDirectoryDatasetStoreManagementProcess $process = null
    ) {

        parent::__construct($data, $model, $manager, $private_data, $process);
    }
}
