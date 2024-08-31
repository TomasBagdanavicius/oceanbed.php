<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\AbstractDatasetSelectHandle;
use LWP\Components\Model\RelationalPropertyModel;
use LWP\Components\Definitions\DefinitionCollectionSet;
use LWP\Components\Datasets\Interfaces\DatasetInterface;

class ArrayCollectionDatasetSelectHandle extends AbstractDatasetSelectHandle
{
    public function __construct(
        DatasetInterface $dataset,
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

        return ArrayCollectionDatasetDataServerContext::class;
    }
}
