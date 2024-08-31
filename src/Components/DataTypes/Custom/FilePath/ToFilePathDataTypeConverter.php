<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\FilePath;

use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToFilePathDataTypeConverter extends DataTypeConverter
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
            StringDataTypeValueContainer::class
        ];
    }


    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): FilePathDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof FilePathDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", FilePathDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof FilePathDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException(sprintf("Value cannot be converted to %s data type value.", FilePathDataType::TYPE_TITLE));
        }

        if (($value instanceof StringDataTypeValueContainer) || ($value instanceof IntegerDataTypeValueContainer)) {
            $value = $value->getValue();
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new FilePathDataTypeValueContainer($value, $value_descriptor);
    }
}
