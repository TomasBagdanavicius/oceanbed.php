<?php

declare(strict_types=1);

namespace LWP\Components\Datasets\ArrayCollectionDataset;

use LWP\Components\Datasets\AbstractDatasetSelectHandle;

class ArrayCollectionDatasetRelationshipIterator extends \IteratorIterator
{
    public readonly array $extrinsic_containers;


    public function __construct(
        \Traversable $iterator,
        public readonly AbstractDatasetSelectHandle $select_handle,
        ?string $class = null
    ) {

        $this->extrinsic_containers = $select_handle->getExtrinsicContainerList();
        parent::__construct($iterator, $class);
    }


    //

    public function current(): mixed
    {

        $current_element = parent::current();

        foreach ($this->extrinsic_containers as $extrinsic_container_name) {

            $container = $this->select_handle->getContainer($extrinsic_container_name);
            $container_name = $container->getPerspective()->container_name;
            $the_other_container_name = $container->getTheOtherPerspective()->container_name;
            $the_other_dataset = $container->getTheOtherDataset();
            $collection = $the_other_dataset->column_array_collection->matchSingleEqualToCondition($the_other_container_name, $current_element[$container_name]);

            if ($collection->count() !== 0) {
                $rel_property_name = $container->extrinsic_container_name;
                $current_element[$extrinsic_container_name] = $collection->getFirst()[$rel_property_name];
            } else {
                // Nullify when the link does not yield any value
                $current_element[$extrinsic_container_name] = null;
            }
        }

        return $current_element;
    }
}
