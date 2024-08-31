<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\Interfaces\DatasetManagementProcessInterface;
use LWP\Components\Datasets\ArrayCollectionDataset\ArrayCollectionDatabase;

class ArrayCollectionDatasetStoreManagementProcess implements DatasetManagementProcessInterface
{
    // Termination status of the process.
    private bool $terminated = false;


    public function __construct(
        public readonly ArrayCollectionDatabase $database,
        public readonly bool $commitment = true,
        public readonly bool $auto_commitment = false
    ) {

    }


    // Fully terminates the process.

    public function terminate(): void
    {

        $this->terminated = true;
    }


    // Tells if process has been terminated.

    public function isTerminated(): bool
    {

        return $this->terminated;
    }


    // Fully commits all procedures in this process.

    public function commit(): void
    {

        // Nothing to be done here
    }


    // Adds a new savepoint.

    public function addSavepoint(string $savepoint_name): void
    {

        // Nothing to be done here
    }


    // Releases the last savepoint.

    public function releaseSavepoint(): void
    {

        // Nothing to be done here
    }
}
