<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\AbstractDatasetCreateManager;

class ArrayCollectionDatasetCreateManager extends AbstractDatasetCreateManager
{
    public function __construct(
        ArrayCollectionDatasetStoreHandle $store_handle,
    ) {

        parent::__construct($store_handle);
    }


    //

    public function getCreateEntryHandlerClassName(): string
    {

        return ArrayCollectionDatasetCreateEntryHandler::class;
    }


    //

    public function manyFromArray(array $data, bool $commit = true): array
    {

        return $this->manyFromArrayMake($data, $commit);
    }
}
