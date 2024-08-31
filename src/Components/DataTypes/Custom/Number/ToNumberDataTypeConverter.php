<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Common\Interfaces\Sizeable;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\DataTypeConverter;
use LWP\Components\DataTypes\Natural\String\StringDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeConversionException;
use LWP\Components\DataTypes\DataTypeValueDescriptor;
use LWP\Components\Rules\NumberFormattingRule;

class ToNumberDataTypeConverter extends DataTypeConverter
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
            NumberDataTypeValueContainer::class,
            Sizeable::class,
            StringDataTypeValueContainer::class,
            IntegerDataTypeValueContainer::class,
        ];
    }


    //

    public static function convert(
        mixed $value,
        ?DataTypeValueDescriptor $value_descriptor = null,
        ?NumberFormattingRule $formatting_rule = null,
        bool $add_validity = true,
        bool $obey_formatting_rule = false
    ): NumberDataTypeValueContainer {

        if ($value_descriptor && !($value_descriptor instanceof NumberDataTypeValueDescriptor)) {
            throw new \TypeError(sprintf("Value descriptor parameter must be an instance of %s.", NumberDataTypeValueDescriptor::class));
        }

        // Self
        if ($value instanceof NumberDataTypeValueContainer) {
            return $value;
        }

        if (!self::canConvertFrom($value)) {
            throw new DataTypeConversionException("Value cannot be converted to number data type value.");
        }

        if (($value instanceof Sizeable) && !($value instanceof StringDataTypeValueContainer) && !($value instanceof IntegerDataTypeValueContainer)) {
            $value = $value->getSize();
        }

        if ($obey_formatting_rule && $formatting_rule) {
            $formatter = $formatting_rule->getFormatter();
            $string_value = $formatter->format($string_value);
        }

        if ($add_validity) {
            $value_descriptor = parent::addValidity($value_descriptor);
        }

        return new NumberDataTypeValueContainer((string)$value, $value_descriptor, $formatting_rule);
    }
}
