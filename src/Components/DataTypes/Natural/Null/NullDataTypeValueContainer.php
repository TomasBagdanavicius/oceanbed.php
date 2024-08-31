<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Null;

use LWP\Components\DataTypes\Natural\NaturalDataTypeValueContainer;

class NullDataTypeValueContainer extends NaturalDataTypeValueContainer implements \Stringable
{
    public function __construct(
        #review: can this property be removed?
        null $value = null,
        ?NullDataTypeValueDescriptor $value_descriptor = null
    ) {

        parent::__construct($value);
    }


    //

    public function __toString(): string
    {

        return NullDataType::TYPE_TITLE;
    }


    // Subclasses should define return type for strictness.

    public function getValue(): null
    {

        return $this->value;
    }
}
