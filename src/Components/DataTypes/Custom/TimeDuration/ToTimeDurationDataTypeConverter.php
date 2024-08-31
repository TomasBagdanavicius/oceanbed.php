<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\TimeDuration;

use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToTimeDurationDataTypeConverter extends DataTypeConverter
{
    //

    public static function getSupportedVariableTypeList(): ?array
    {

        return [
            'string',
        ];
    }


    //

    public static function getSupportedClassNameList(): ?array
    {

        return [
            StringDataTypeValueContainer::class,
            TimeDurationDataTypeValueContainer::class,
            \DateInterval::class,
        ];
    }


    // Convers DateInterval object into the ISO 8601 temporal duration string.
    // See: https://en.wikipedia.org/wiki/ISO_8601#Durations

    public static function convertDateIntervalToISO8601Duration(\DateInterval $date_interval): string
    {

        $data = array_combine(
            /* No support for milliseconds or microseconds, because PHP doesn't have a designator for it, see: https://www.php.net/manual/en/dateinterval.construct.php#refsect1-dateinterval.construct-parameters */
            ['Y', 'M', 'D', 'H', 'I', 'S'],
            explode(',', $date_interval->format('%y,%m,%d,%h,%i,%s'))
        );

        $result = 'P';
        $is_time_portion = false;

        foreach ($data as $designator => $integer) {

            if ($integer) {

                $result .= $integer . (($is_time_portion && $designator === 'I')
                    ? 'M'
                    : $designator);
            }

            if ($designator === 'D') {

                $result .= 'T';
                $is_time_portion = true;
            }
        }

        return $result;
    }


    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): TimeDurationDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof TimeDurationDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", TimeDurationDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof TimeDurationDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {

            throw new DataTypeConversionException(
                sprintf("Value cannot be converted to %s data type value.", TimeDurationDataType::TYPE_TITLE)
            );
        }

        if (($value instanceof StringDataTypeValueContainer)) {
            $value = $value->getValue();
        }

        if (($value instanceof \DateInterval)) {

            $value = self::convertDateIntervalToISO8601Duration($value);

        } elseif (!str_starts_with($value, 'P')) {

            if ($date_interval = \DateInterval::createFromDateString($value)) {

                $value = self::convertDateIntervalToISO8601Duration($date_interval);

            } else {

                throw new DataTypeConversionException(sprintf(
                    "Value cannot be converted to %s data type value.",
                    TimeDurationDataType::TYPE_TITLE
                ));
            }
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new TimeDurationDataTypeValueContainer($value, $value_descriptor);
    }
}
