<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\AbstractDatasetCreateEntryHandler;

class ArrayCollectionDatasetCreateEntryHandler extends AbstractDatasetCreateEntryHandler
{
    public function __construct(
        array $data,
        ArrayCollectionDatasetCreateManager $create_manager,
        $process = null,
    ) {

        parent::__construct($data, $create_manager, $process);
    }
}
