<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\TimeDuration;

use LWP\Components\DataTypes\Custom\CustomDataType;

class TimeDurationDataType extends CustomDataType
{
    public const TYPE_NAME = 'date-interval';
    public const TYPE_TITLE = 'Date Interval';


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

    public static function getSupportedFormattingRuleList(): ?array
    {

        return null;
    }


    //

    public static function hasBuilder(): bool
    {

        return false;
    }
}
