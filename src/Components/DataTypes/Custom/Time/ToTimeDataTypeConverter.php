<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Time;

use LWP\Common\DateTime;
use LWP\Components\DataTypes\Custom\DateTime\ToDateTimeDataTypeConverter;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToTimeDataTypeConverter extends ToDateTimeDataTypeConverter
{
    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): TimeDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof DateDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", DateDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof TimeDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException("Value cannot be converted to date-time data type value.");
        }

        if (($value instanceof IntegerDataTypeValueContainer)) {
            // Gets integer value.
            $value = $value->getValue();
        }

        // Create from timestamp. Either integer or numeric string.
        if (is_numeric($value)) {

            #review: is this necessary when "setTimestamp" is used below?
            if (!DateTime::isValidTimeStamp($value)) {
                throw new DataTypeConversionException("Given date-time timestamp ($value) is invalid.");
            }

            $date_object = (new \DateTime())->setTimestamp(intval($value));

            // Create from date-time string.
        } elseif (is_string($value) || ($value instanceof StringDataTypeValueContainer)) {

            $date_object = new \DateTime($value);

            // It's a "DateTime" object.
        } elseif (($value instanceof \DateTime)) {

            $date_object = $value;
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new TimeDataTypeValueContainer(
            intval($date_object->format('H')),
            intval($date_object->format('I')),
            intval($date_object->format('S')),
        );
    }
}
