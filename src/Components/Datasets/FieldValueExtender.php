<?php

declare(strict_types=1);

namespace LWP\Components\Datasets;

use LWP\Components\Datasets\Interfaces\DatasetInterface;
use LWP\Components\Datasets\Interfaces\DatasetFieldValueExtenderInterface;
use LWP\Components\Datasets\Interfaces\DataServerInterface;
use LWP\Components\Datasets\SpecialContainerCollection;

class FieldValueExtender implements DatasetFieldValueExtenderInterface, \Stringable
{
    public function __construct(
        public readonly int $property_value,
        public readonly string $property_name,
        public readonly SpecialContainerCollection $containers
    ) {

    }


    //

    public function __toString(): string
    {

        return (string)$this->property_value;
    }


    //

    public function getOriginalValue(): int
    {

        return $this->property_value;
    }


    //

    public function getForeignObject(): DataServerInterface
    {

        return $this->containers->resolveRelationalProperty($this->property_name, $this->property_value);
    }
}
