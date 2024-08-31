<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\AbstractDatasetSelectHandle;

class FilesystemDirectoryDatasetSelectHandle extends AbstractDatasetSelectHandle
{
    public function __construct(
        FilesystemDirectoryDataset $dataset,
        array $identifiers,
        array $modifiers = [],
        ?string $model_class_name = null,
        array $model_class_extras = []
    ) {

        parent::__construct($dataset, $identifiers, $modifiers, $model_class_name, $model_class_extras);
    }


    //

    public function getDataServerContextClassName(): string
    {

        return FilesystemDirectoryDatasetDataServerContext::class;
    }
}
