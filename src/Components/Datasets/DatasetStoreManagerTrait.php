<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\FileLogger;

trait DatasetStoreManagerTrait
{
    //

    public function getProcess(bool $commit): array
    {

        if (!$this->process) {

            $class_name = $this->store_handle->getDatasetStoreManagementProcessClassName();
            $process = new ($class_name)(
                $this->store_handle->dataset->database,
                $commit
            );
            $this->process = $process;

            return [
                'process' => $process,
                'inherited_process' => false
            ];

        } else {

            return [
                'process' => $this->process,
                'inherited_process' => true
            ];
        }
    }
}
