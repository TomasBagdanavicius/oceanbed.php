<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Common\Array\ArrayCollection;
use LWP\Components\Datasets\AbstractDatabase;
use LWP\Components\Datasets\Interfaces\DatabaseInterface;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Interfaces\DatabaseDescriptorInterface;
use LWP\Components\Datasets\ConsistentDatasetCollection;

class ArrayCollectionDatabase extends AbstractDatabase implements DatabaseInterface
{
    public readonly ArrayCollection $collection;
    public readonly DatabaseDescriptorInterface $descriptor;


    public function __construct(
        public readonly string $descriptor_class_name = DefaultArrayCollectionDatabaseDescriptor::class
    ) {

        if ($descriptor_class_name !== DefaultArrayCollectionDatabaseDescriptor::class) {

            if (!class_exists($descriptor_class_name)) {
                throw new \ValueError("Descriptor class name must represent an existing class");
            }

            if (!is_subclass_of($descriptor_class_name, DatabaseDescriptorInterface::class)) {
                throw new \ValueError(sprintf(
                    "Descriptor class must implement %s",
                    DatabaseDescriptorInterface::class
                ));
            }
        }

        $this->collection = new ConsistentDatasetCollection();
        $this->descriptor = new $descriptor_class_name($this);

        parent::__construct();
    }


    //

    public function hasAddress(string $address_name): bool
    {

        return $this->collection->containsKey($address_name);
    }


    //

    public function initDataset(string $address_name, array $extra_params = []): ArrayCollectionDataset
    {

        unset($extra_params['dataset_name'], $extra_params['database']);

        $params = [
            ...$extra_params,
            'dataset_name' => $address_name,
            'database' => $this
        ];
        $dataset = new ArrayCollectionDataset(...$params);

        $this->collection->add($dataset);

        return $dataset;
    }


    // Tells if database supports multi-queries.

    public function supportsMultiQuery(): false
    {

        return false;
    }


    // Returns database descriptor object

    public function getDescriptor(): DatabaseDescriptorInterface
    {

        return $this->descriptor;
    }


    // Returns field value's formatter object

    public function getStoreFieldValueFormatter(): ArrayCollectionDatabaseStoreFieldValueFormatter
    {

        return new ArrayCollectionDatabaseStoreFieldValueFormatter($this);
    }
}
