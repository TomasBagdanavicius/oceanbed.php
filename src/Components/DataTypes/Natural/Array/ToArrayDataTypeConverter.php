<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Array;

use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToArrayDataTypeConverter extends DataTypeConverter
{
    //

    public static function getSupportedVariableTypeList(): ?array
    {

        return [
            'array',
        ];
    }


    //

    public static function getSupportedClassNameList(): ?array
    {

        return [
            ArrayDataTypeValueContainer::class,
        ];
    }


    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): ArrayDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof ArrayDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", ArrayDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof ArrayDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException("Value cannot be converted to array data type value.");
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new ArrayDataTypeValueContainer($value, $value_descriptor);
    }
}
