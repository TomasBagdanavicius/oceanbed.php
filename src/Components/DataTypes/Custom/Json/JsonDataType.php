<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Json;

use LWP\Components\DataTypes\Custom\CustomDataType;

class JsonDataType extends CustomDataType
{
    public const TYPE_NAME = 'json';
    public const TYPE_TITLE = 'JSON';


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
