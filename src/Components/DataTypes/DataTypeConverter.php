<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes;

use LWP\Common\Common;
use LWP\Common\Enums\ValidityEnum;
use LWP\Components\DataTypes\DataTypeValueContainer;

abstract class DataTypeConverter
{
    //

    abstract public static function getSupportedVariableTypeList(): ?array;


    //

    abstract public static function getSupportedClassNameList(): ?array;


    //

    abstract public static function convert(mixed $value, ?DataTypeValueDescriptor $value_descriptor = null): DataTypeValueContainer;


    //

    public static function getValueContainerClassName(): string
    {

        return Common::getNamespaceDirname(static::class)
            . '\\'
            // Shift off "To" and pop off "Converter"
            . substr(Common::getNamespaceBasename(static::class), 2, -9)
            . 'ValueContainer';
    }


    //

    public static function addValidity(?DataTypeValueDescriptor $value_descriptor): DataTypeValueDescriptor
    {

        /* Value has been successfully converted, so it means that it's valid. Apart from common logic, this is useful for another reason. When formatting rule is passed into `convert` method (eg. datetime, number types), it will be taken into account inside value container, but this is not always desired when coming directly from convertion, eg. when convertion is utilized just to validate data type. */
        if ($value_descriptor) {
            $value_descriptor->validity = ValidityEnum::VALID;
        } else {
            $value_descriptor_class_name = self::getValueContainerClassName()::getDescriptorClassName()::getValueDescriptorClassName();
            $value_descriptor = new $value_descriptor_class_name(ValidityEnum::VALID);
        }

        return $value_descriptor;
    }


    //

    public static function getAllSupportedTypeList(): ?array
    {

        $variable_type_list = static::getSupportedVariableTypeList();
        $class_name_list = static::getSupportedClassNameList();

        if ($variable_type_list && $class_name_list) {
            return array_merge(static::getSupportedVariableTypeList(), $class_name_list);
        } elseif ($variable_type_list) {
            return $variable_type_list;
        } elseif ($class_name_list) {
            return $class_name_list;
        } else {
            return null;
        }
    }


    //

    public static function canConvertFrom(mixed $value): bool
    {

        return (
            (is_object($value) && in_array($value::class, static::getSupportedClassNameList()))
            || in_array(gettype($value), static::getSupportedVariableTypeList())
        );
    }
}
