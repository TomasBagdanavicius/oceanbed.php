<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\EmailAddress;

use LWP\Components\DataTypes\Custom\CustomDataType;

class EmailAddressDataType extends CustomDataType
{
    public const TYPE_NAME = 'email_address';
    public const TYPE_TITLE = 'Email Address';


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
