<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural;

class NaturalDataTypeFactory
{
    //

    public static function getDataTypeList(): array
    {

        return [
            'string',
            'integer',
            'boolean',
            'null',
            'array',
        ];
    }


    //

    public static function getDataTypeValueClassNameMap(): array
    {

        return [
            'string' => (__NAMESPACE__ . '\String\StringDataTypeValueContainer'),
            'integer' => (__NAMESPACE__ . '\Integer\IntegerDataTypeValueContainer'),
            'boolean' => (__NAMESPACE__ . '\Boolean\BooleanDataTypeValueContainer'),
            'null' => (__NAMESPACE__ . '\Null\NullDataTypeValueContainer'),
            'array' => (__NAMESPACE__ . '\Array\ArrayDataTypeValueContainer'),
        ];
    }


    // Gets PHP's variable type list.

    public static function getVariableTypeList(): array
    {

        return [
            'boolean',
            'integer',
            'double',
            'string',
            'array',
            'object',
            'resource',
            'resource (closed)',
            'null',
        ];
    }


    //

    public static function getDataTypeClassNameFromMixedTypeVariable(mixed $value): string
    {

        $variable_type = gettype($value);

        if ($variable_type === 'resource') {
            throw new \RuntimeException("Type \"$variable_type\" is not supported.");
        }

        if ($variable_type === 'object' && $value::class !== 'stdClass') {
            throw new \RuntimeException(sprintf("Only \"stdClass\" class objects supported, given %s.", $value::class));
        }

        return self::getDataTypeValueClassNameMap()[strtolower($variable_type)];
    }


    //

    public static function createDataTypeValueFromMixedTypeVariable(mixed $value): NaturalDataTypeValueContainer
    {

        return new (self::getDataTypeClassNameFromMixedTypeVariable($value))($value);
    }
}
