<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Json;

use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Network\Exceptions\LongIsNotAnIpAddressException;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueDescriptor;

class ToJsonDataTypeConverter extends DataTypeConverter
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
        ];
    }


    //

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): JsonDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof JsonDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", JsonDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof JsonDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException(sprintf("Value cannot be converted to %s data type value.", JsonDataType::TYPE_TITLE));
        }

        if ($value instanceof StringDataTypeValueContainer) {
            $value = $value->getValue();
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new JsonDataTypeValueContainer($value, $value_descriptor);
    }
}
