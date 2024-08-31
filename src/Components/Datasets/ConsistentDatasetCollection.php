<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Common\Collectable;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class ConsistentDatasetCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = []
    ) {

        $database = null;

        // Allow for valid constraint object classes to be added only.
        parent::__construct($data, element_filter: function (mixed $element, null|int|string $key) use (&$database): true {

            if (!($element instanceof DatasetInterface)) {
                throw new InvalidMemberException(sprintf("Collection %s accepts elements of class %s only", self::class, DatasetInterface::class));
            }

            $element_database_class = $element->getDatabase()::class;

            if (!$database) {
                $database = $element_database_class;
            } elseif ($database != $element_database_class) {
                throw new \Exception(sprintf("Element %s is inconsistent with the primary database.", $element_database_class));
            }

            return true;

            // Use element class name as the name identifier in the collection.
        }, obtain_name_filter: function (mixed $element): string {

            return $element->name;

        });
    }


    // Creates a new relationship object instance and attaches it to this collection.

    public function createNewMember(array $params = []): Collectable
    {

        $dataset = new ($params['dataset_class_name']);
        $dataset->registerCollection($this, $this->add($dataset));

        return $dataset;
    }
}
