<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\Exceptions\NotFoundException;

trait IndexableTrait
{
    //

    public function hasIndexableProperty(string $property_name): bool
    {

        return in_array($property_name, $this->getIndexablePropertyList());
    }


    //

    public function assertIndexablePropertyExistence(string|array $value): void
    {

        if (is_string($value)) {

            if (!self::hasIndexableProperty($value)) {
                throw new NotFoundException(sprintf(
                    "Indexable property \"%s\" was not found",
                    $value
                ));
            }

        } else {

            $property_diff = array_diff($value, $this->getIndexablePropertyList());

            if ($property_diff) {
                throw new NotFoundException(sprintf(
                    "Some indexable properties were not found: %s",
                    ('"' . implode('", "', $property_diff) . '"')
                ));
            }
        }
    }


    // Gets indexable data representing this object

    public function getIndexableData(array $property_list = []): array
    {

        if ($property_list) {
            $this->assertIndexablePropertyExistence($property_list);
        } else {
            $property_list = $this->getIndexablePropertyList();
        }

        $data = [];

        foreach ($property_list as $property_name) {
            $data[$property_name] = $this->getIndexablePropertyValue($property_name);
        }

        return $data;
    }


    //

    public function updateIndexableEntry(int|string $name, int|string $value): void
    {

        if (!empty($this->collections)) {

            foreach ($this->collections as $collection_data) {

                if (method_exists($collection_data[0], 'updateEntry')) {

                    $collection_data[0]->updateEntry($collection_data[1], $name, $value);
                }
            }
        }
    }
}
