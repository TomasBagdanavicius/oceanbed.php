<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\IpAddress;

use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Network\IpAddress;
use LWP\Network\Exceptions\LongIsNotAnIpAddressException;

class ToIpAddressDataTypeConverter extends DataTypeConverter
{
    //

    public static function getSupportedVariableTypeList(): ?array
    {

        return [
            'string',
            'integer',
        ];
    }


    //

    public static function getSupportedClassNameList(): ?array
    {

        return [
            StringDataTypeValueContainer::class,
            IntegerDataTypeValueContainer::class,
            IpAddressDataTypeValueContainer::class,
        ];
    }


    //
    /* Hexadecimal is not supported, because (1) it can be ambiguos in comparison to long (eg. 16909060 (as long) -> 1.2.3.4 or 16909060 (as hexadecimal) -> 22.144.144.96) and (2) it's not really essential to have it. */

    public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null, bool $add_validity = true): IpAddressDataTypeValueContainer
    {

        if ($value_descriptor && !($value_descriptor instanceof IpAddressDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", IpAddressDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof IpAddressDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException(sprintf("Value cannot be converted to %s data type value.", IpAddressDataType::TYPE_TITLE));
        }

        if (($value instanceof StringDataTypeValueContainer) || ($value instanceof IntegerDataTypeValueContainer)) {
            $value = $value->getValue();
        }

        // Integer or all numbers.
        if (is_int($value) || ctype_digit($value)) {

            try {

                $value = IpAddress::fromLong((int)$value)->__toString();

            } catch (LongIsNotAnIpAddressException $exception) {

                throw new DataTypeConversionException(
                    message: sprintf("Value cannot be converted to %s data type value: %s", IpAddressDataType::TYPE_TITLE, $exception->getMessage()),
                    previous: $exception
                );
            }
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new IpAddressDataTypeValueContainer($value, $value_descriptor);
    }
}
