<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom;

class CustomDataTypeFactory
{
    //

    public static function getDataTypeList(): array
    {

        return array_keys(self::getDataTypeValueClassNameMap());
    }


    //

    public static function getDataTypeValueClassNameMap(): array
    {

        return [
            'number' => (__NAMESPACE__ . '\Number\NumberDataTypeValueContainer'),
            'datetime' => (__NAMESPACE__ . '\DateTime\DateTimeDataTypeValueContainer'),
            'date' => (__NAMESPACE__ . '\Date\DateDataTypeValueContainer'),
            'email_address' => (__NAMESPACE__ . '\EmailAddress\EmailAddressDataTypeValueContainer'),
            'ip_address' => (__NAMESPACE__ . '\IpAddress\IpAddressDataTypeValueContainer'),
            'time_duration' => (__NAMESPACE__ . '\TimeDuration\TimeDurationDataTypeValueContainer'),
            'json' => (__NAMESPACE__ . '\Json\JsonDataTypeValueContainer'),
            'file_path' => (__NAMESPACE__ . '\FilePath\FilePathDataTypeValueContainer'),
        ];
    }


    //

    public static function getTypeObjectClassNameByTypeName(string $type): string
    {

        return self::getDataTypeValueClassNameMap()[$type];
    }
}
