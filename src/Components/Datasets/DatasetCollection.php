<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Common\Array\RepresentedClassObjectCollection;
use LWP\Common\Collections\ClassObjectCollection;
use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Common\Collectable;
use LWP\Common\Collections\Exceptions\InvalidMemberException;

class DatasetCollection extends RepresentedClassObjectCollection implements ClassObjectCollection
{
    public function __construct(
        array $data = [],
    ) {

        // Allow for valid constraint object classes to be added only.
        parent::__construct($data, element_filter: function (mixed $element, null|int|string $key): true {

            if (!($element instanceof DatasetInterface)) {
                throw new InvalidMemberException(sprintf("Collection %s accepts elements of class %s only", self::class, DatasetInterface::class));
            }

            return true;

            // Use element class name as the name identifier in the collection.
        }, obtain_name_filter: function (mixed $element): ?string {

            if ($element instanceof DatasetInterface) {
                return $element::class;
            }

            return null;

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
