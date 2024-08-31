<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\EmailAddress;

use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToEmailAddressDataTypeConverter extends DataTypeConverter
{
    //

    public static function getSupportedVariableTypeList(): ?array
    {

        return [
            'string'
        ];
    }


    //

    public static function getSupportedClassNameList(): ?array
    {

        return [
            StringDataTypeValueContainer::class,
            EmailAddressDataTypeValueContainer::class
        ];
    }


    //
    // Positive `$add_validity` doesn't make sense in this data type, because conversion does not ensure valid email address.

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = false): EmailAddressDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof EmailAddressDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", EmailAddressDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof EmailAddressDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException(sprintf("Value cannot be converted to %s data type value.", EmailAddressDataType::TYPE_TITLE));
        }

        if (($value instanceof StringDataTypeValueContainer)) {
            $value = $value->getValue();
        }

        return new EmailAddressDataTypeValueContainer($value, $value_descriptor);
    }
}
