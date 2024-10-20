<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Json;

use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\Custom\CustomDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;

class JsonDataTypeValueContainer extends CustomDataTypeValueContainer
{
    public function __construct(
        mixed $value,
        ?JsonDataTypeValueDescriptor $value_descriptor = null
    ) {

        if (!$value_descriptor || $value_descriptor->validity === ValidityEnum::UNDETERMINED) {
            $validator = JsonDataType::getValidatorClassObject($value);
        }

        if (
            (isset($validator) && !$validator->validate())
            // One cannot submit invalid values as indicated by descriptor
            || ($value_descriptor && $value_descriptor->validity === ValidityEnum::INVALID)
        ) {
            throw new DataTypeError(sprintf(
                "Value is not of \"%s\" type",
                JsonDataType::TYPE_NAME
            ));
        }

        parent::__construct($value, $value_descriptor, ($validator ?? null));
    }


    //

    public function __toString(): string
    {

        return $this->value;
    }


    //

    public function getValue(): string // Defines the return data type.
    {
        return $this->value;
    }
}
