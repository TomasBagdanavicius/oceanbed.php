<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Common\Common;
use LWP\Components\Datasets\AbstractDatasetCreateManager;
use LWP\Components\Datasets\Enums\DatasetActionStatusEnum;
use LWP\Components\Datasets\Exceptions\CreateEntryException;

class FilesystemDirectoryDatasetCreateManager extends AbstractDatasetCreateManager
{
    public function __construct(
        FilesystemDirectoryDatasetStoreHandle $store_handle,
    ) {

        parent::__construct($store_handle);
    }


    //

    public function getCreateEntryHandlerClassName(): string
    {

        return FilesystemDirectoryDatasetCreateEntryHandler::class;
    }


    //

    public function manyFromArray(array $data, bool $commit = true): array
    {

        return $this->manyFromArrayMake($data, $commit);
    }
}
