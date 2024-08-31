<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\IpAddress;

use LWP\Components\DataTypes\Custom\CustomDataType;

class IpAddressDataType extends CustomDataType
{
    public const TYPE_NAME = 'ip_address';
    public const TYPE_TITLE = 'IP Address';


    //

    public static function getSupportedConstraintClassNameList(): ?array
    {

        return null;
    }


    //

    public static function getSupportedDefinitionList(): ?array
    {

        return null;
    }


    //

    public static function hasBuilder(): bool
    {

        return false;
    }
}
