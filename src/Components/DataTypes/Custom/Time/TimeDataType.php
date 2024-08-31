<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Time;

use LWP\Components\DataTypes\Custom\CustomDataType;

class TimeDataType extends CustomDataType
{
    public const TYPE_NAME = 'time';
    public const TYPE_TITLE = 'Time';


    //

    public static function getSupportedConstraintClassNameList(): ?array
    {

        return [
            'LWP\Components\Constraints\MinSizeConstraint',
            'LWP\Components\Constraints\MaxSizeConstraint',
        ];
    }


    //

    public static function getSupportedDefinitionList(): ?array
    {

        return [
            // Constraints
            'min',
            'max',
        ];
    }


    //

    public static function hasBuilder(): bool
    {

        return false;
    }
}
