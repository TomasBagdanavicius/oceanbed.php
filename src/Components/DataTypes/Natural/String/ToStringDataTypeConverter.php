<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToStringDataTypeConverter extends DataTypeConverter
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
            \Stringable::class,
            IntegerDataTypeValueContainer::class,
        ];
    }


    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): StringDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof StringDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", StringDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof StringDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException("Value cannot be converted to string data type value.");
        }

        // All remaining supported types should be string capable.
        if (!is_string($value)) {
            $value = (string)$value;
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new StringDataTypeValueContainer($value, $value_descriptor);
    }
}
