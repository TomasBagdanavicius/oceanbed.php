<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes;

use LWP\Components\DataTypes\Exceptions\UnrecognizedDataTypeException;

class DataTypeFactory
{
    //

    public static function getDataTypeList(): array
    {

        return array_merge(
            Natural\NaturalDataTypeFactory::getDataTypeList(),
            Custom\CustomDataTypeFactory::getDataTypeList(),
        );
    }


    //

    public static function getDataTypeValueClassNameMap(): array
    {

        return array_merge(
            Natural\NaturalDataTypeFactory::getDataTypeValueClassNameMap(),
            Custom\CustomDataTypeFactory::getDataTypeValueClassNameMap()
        );
    }


    //

    public static function getDataTypeValueClassNameByTypeName(string $type): string
    {

        $map = self::getDataTypeValueClassNameMap();

        if (!isset($map[$type])) {
            throw new UnrecognizedDataTypeException(sprintf(
                "Unrecognized data type \"%s\"",
                $type
            ));
        }

        return $map[$type];
    }
}
