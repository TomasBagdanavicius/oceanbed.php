<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Array;

use LWP\Components\DataTypes\Natural\NaturalDataTypeValueContainer;
use LWP\Common\Interfaces\Sizeable;
use LWP\Common\Array\ArrayCollection;

class ArrayDataTypeValueContainer extends NaturalDataTypeValueContainer implements Sizeable, \JsonSerializable
{
    public function __construct(
        array $value,
        ?ArrayDataTypeValueDescriptor $value_descriptor = null,
    ) {

        parent::__construct($value, $value_descriptor);
    }


    // Defines custom return type for strictness.

    public function getValue(): array // Just for strictness.
    {
        return $this->value;
    }


    //

    public function getSize(): int
    {

        return count($this->array);
    }


    // Converts array to "ArrayCollection".

    public function toArrayCollection(): ArrayCollection
    {

        return new ArrayCollection($this->array);
    }


    // Exports data in JSON format.

    public function jsonSerialize(): mixed
    {

        return json_encode($this->array, JSON_THROW_ON_ERROR);
    }
}
