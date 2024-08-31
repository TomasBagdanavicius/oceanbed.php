<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Boolean;

use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\DataTypeValueContainer;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToBooleanDataTypeConverter extends DataTypeConverter
{
    //

    public static function getSupportedVariableTypeList(): ?array
    {

        return [
            'boolean',
            'integer',
            'string',
        ];
    }


    //

    public static function getSupportedClassNameList(): ?array
    {

        return [
            StringDataTypeValueContainer::class,
            IntegerDataTypeValueContainer::class,
            BooleanDataTypeValueContainer::class,
        ];
    }


    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): BooleanDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof BooleanDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", BooleanDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof BooleanDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException("Value cannot be converted to boolean data type value.");
        }

        if (is_bool($value)) {
            return new BooleanDataTypeValueContainer($value, $value_descriptor);
        }

        if ($value instanceof DataTypeValueContainer) {
            $value = $value->__toString();
        } elseif ($value instanceof IntegerDataTypeValueContainer) {
            $value = $value->getValue();
        }

        if (is_integer($value)) {
            // Only zero is considered "false"
            $value = ($value === 0) ? 'false' : 'true';
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        if ($value === 'true' || $value === '1') {
            return new BooleanDataTypeValueContainer(true, $value_descriptor);
        } elseif ($value === 'false' || $value === '0') {
            return new BooleanDataTypeValueContainer(false, $value_descriptor);
        }

        throw new DataTypeConversionException("Value cannot be converted to boolean data type value.");
    }
}
