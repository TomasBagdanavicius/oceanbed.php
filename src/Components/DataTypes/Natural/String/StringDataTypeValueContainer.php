<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\Exceptions\DataTypeError;
use LWP\Components\DataTypes\Natural\NaturalDataTypeValueContainer;
use LWP\Common\Interfaces\Sizeable;

class StringDataTypeValueContainer extends NaturalDataTypeValueContainer implements Sizeable, \Stringable
{
    public function __construct(
        mixed $value,
        ?StringDataTypeValueDescriptor $value_descriptor = null,
    ) {

        if (!$value_descriptor || $value_descriptor->validity === ValidityEnum::UNDETERMINED) {
            $validator = StringDataType::getValidatorClassObject($value);
        }

        if (
            (isset($validator) && !$validator->validate())
            // One cannot submit invalid values as indicated by descriptor
            || ($value_descriptor && $value_descriptor->validity === ValidityEnum::INVALID)
        ) {
            throw new DataTypeError(sprintf("Value is not of \"%s\" type.", StringDataType::TYPE_NAME));
        }

        parent::__construct($value, $value_descriptor, ($validator ?? null));
    }


    //

    public function __toString(): string
    {

        return $this->value;
    }


    // Defines custom return type for strictness.

    public function getValue(): string // Just for strictness.
    {
        return $this->value;
    }


    // Sizeable.

    public function getSize(): int
    {

        return $this->getParser()->getLength();
    }
}
