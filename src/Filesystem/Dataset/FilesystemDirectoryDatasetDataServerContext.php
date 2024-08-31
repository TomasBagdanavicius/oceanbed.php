<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\AbstractDatasetDataServerContext;
use LWP\Components\Model\BasePropertyModel;
use LWP\Components\Model\EnhancedPropertyModel;
use LWP\Components\Datasets\Interfaces\DataServerInterface;
use LWP\Components\Datasets\Interfaces\DatasetResultInterface;

class FilesystemDirectoryDatasetDataServerContext extends AbstractDatasetDataServerContext implements DataServerInterface
{
    public function __construct(
        FilesystemDirectoryDatasetFetchManager $fetch_manager,
        BasePropertyModel $model,
        DatasetResultInterface $result,
        ?int $no_limit_count = null,
        ?EnhancedPropertyModel $action_params = null,
        ?EnhancedPropertyModel $filter_params = null
    ) {

        parent::__construct($fetch_manager, $model, $result, $no_limit_count, $action_params, $filter_params);
    }
}
