<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Integer;

use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Common\Interfaces\Sizeable;
use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToIntegerDataTypeConverter extends DataTypeConverter
{
    //

    public static function getSupportedVariableTypeList(): ?array
    {

        return [
            'string',
            'integer',
            'double',
        ];
    }


    //

    public static function getSupportedClassNameList(): ?array
    {

        return [
            StringDataTypeValueContainer::class,
            IntegerDataTypeValueContainer::class,
            DateTimeDataTypeValueContainer::class,
            Sizeable::class,
        ];
    }


    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): IntegerDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof IntegerDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", IntegerDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof IntegerDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException("Value cannot be converted to integer data type value.");
        }

        if ($value instanceof DateTimeDataTypeValueContainer) {

            $value = strtotime($value->__toString());

        } elseif (($value instanceof DataTypeValueContainer) || ($value instanceof \Stringable) || gettype($value) === 'string') {

            if (is_object($value)) {
                $value = $value->__toString();
            }

            $is_numeric = is_numeric($value);

            // Attempt to convert from string time
            if (!$is_numeric && ($strtotime = strtotime($value))) {
                $value = $strtotime;
            } elseif (!$is_numeric) {
                throw new DataTypeConversionException("Given value cannot be converted to integer data type value.");
            }

            // Sizeable interface.
        } elseif ($value instanceof Sizeable) {

            $value = $value->getSize();
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new IntegerDataTypeValueContainer((int)$value, $value_descriptor);
    }
}
