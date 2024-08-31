<?php

declare(strict_types=1);

namespace LWP\Filesystem\Dataset;

use LWP\Components\Datasets\AbstractDatabase;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Filesystem\Filesystem;
use LWP\Filesystem\FileType\Directory;
use LWP\Filesystem\Path\PathEnvironmentRouter;

class FilesystemDatabase extends AbstractDatabase implements DatabaseInterface
{
    // Tells if a dataset exists by a given address name

    public function hasAddress(string $address_name): bool
    {

        return (file_exists($address_name) && is_dir($address_name));
    }


    // Validates dataset name

    public function validateDatasetName(string $dataset_name, int $char_limit = 30): true
    {

        // Always valid, because dataset name is a pathname that represents an existing directory in the filesystem
        return true;
    }


    // Returns a new dataset object instance by a given address name

    public function initDataset(string $address_name, array $extra_params = []): DatasetInterface
    {

        Filesystem::exists($address_name);
        $file_path = PathEnvironmentRouter::getStaticInstance()::getFilePathInstance($address_name);
        $directory = new Directory($file_path, ...$extra_params);

        return new FilesystemDirectoryDataset($directory);
    }


    // Tells if database supports multi-queries

    public function supportsMultiQuery(): false
    {

        return false;
    }


    // Returns a new database descriptor object instance

    public function getDescriptor(): FilesystemDatabaseDescriptor
    {

        return new FilesystemDatabaseDescriptor($this);
    }


    // Returns a new database field value formatter object instance

    public function getStoreFieldValueFormatter(): FilesystemDatabaseStoreFieldValueFormatter
    {

        return new FilesystemDatabaseStoreFieldValueFormatter($this);
    }
}
