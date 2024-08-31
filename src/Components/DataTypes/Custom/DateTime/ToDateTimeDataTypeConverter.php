<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\DateTime;

use LWP\Common\Common;
use LWP\Common\DateTime;
use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\Rules\DateTimeFormattingRule;

class ToDateTimeDataTypeConverter extends DataTypeConverter
{
    //

    public static function getSupportedVariableTypeList(): ?array
    {

        return [
            'string',
            'integer'
        ];
    }


    //

    public static function getSupportedClassNameList(): ?array
    {

        return [
            \DateTime::class,
            StringDataTypeValueContainer::class,
            IntegerDataTypeValueContainer::class,
            DateTimeDataTypeValueContainer::class
        ];
    }


    //

    public static function convert(
        mixed $value,
        ?DataTypeValueDescriptor $value_descriptor = null,
        ?DateTimeFormattingRule $formatting_rule = null,
        bool $add_validity = true,
        bool $obey_formatting_rule = false
    ): DateTimeDataTypeValueContainer {

        if ($value_descriptor && !($value_descriptor instanceof DateTimeDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", DateTimeDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof DateTimeDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException("Value cannot be converted to date-time data type value.");
        }

        if ($value instanceof IntegerDataTypeValueContainer) {
            // Gets integer value.
            $value = $value->getValue();
        }

        // Create from timestamp. Either integer or numeric string.
        // If timestamp is smaller than current year, make it a year
        if (is_numeric($value) && $value > (int)date('Y')) {

            #review: is this necessary when "setTimestamp" is used below?
            if (!DateTime::isValidTimeStamp($value)) {
                throw new DataTypeConversionException("Given date-time timestamp ($value) is invalid.");
            }

            $string_value = (new \DateTime())->setTimestamp(intval($value))->format(DateTimeDataTypeValueContainer::DEFAULT_FORMAT);

            // Create from date-time string.
        } elseif (is_string($value) || ($value instanceof StringDataTypeValueContainer)) {

            $string_value = (string)$value;

            // It's a "DateTime" object.
        } elseif ($value instanceof \DateTime) {

            $string_value = $value->format(DateTimeDataTypeValueContainer::DEFAULT_FORMAT);
        }

        // eg. when age special format comes in (which is smaller than current year)
        if (!isset($string_value)) {
            $string_value = $value;
        }

        if ($obey_formatting_rule && $formatting_rule) {
            $formatter = $formatting_rule->getFormatter();
            $string_value = $formatter->format($string_value);
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new DateTimeDataTypeValueContainer($string_value, $value_descriptor, $formatting_rule);
    }
}
