<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Boolean;

use LWP\Components\DataTypes\Natural\NaturalDataTypeValueContainer;
use LWP\Common\Interfaces\Sizeable;

class BooleanDataTypeValueContainer extends NaturalDataTypeValueContainer implements Sizeable, \Stringable
{
    public function __construct(
        bool $value,
        ?BooleanDataTypeValueDescriptor $value_descriptor = null,
    ) {

        parent::__construct($value, $value_descriptor);
    }


    //

    public function __toString(): string
    {

        return ($this->value)
            ? 'true'
            : 'false';
    }


    // Subclasses should define return type for strictness.

    public function getValue(): bool
    {

        return $this->value;
    }


    //

    public function getSize(): int
    {

        return intval($this->value);
    }
}
