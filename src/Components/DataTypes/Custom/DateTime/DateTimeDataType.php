<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\DateTime;

use LWP\Components\DataTypes\Custom\CustomDataType;

class DateTimeDataType extends CustomDataType
{
    public const TYPE_NAME = 'datetime';
    public const TYPE_TITLE = 'Date-Time';


    //

    public static function getSupportedConstraintClassNameList(): ?array
    {

        return [
            'LWP\Components\Constraints\MinSizeConstraint',
            'LWP\Components\Constraints\MaxSizeConstraint'
        ];
    }


    //

    public static function getSupportedDefinitionList(): ?array
    {

        return [
            // Constraints
            'min',
            'max'
        ];
    }


    //

    public static function getSupportedFormattingRuleList(): ?array
    {

        return [
            'LWP\Components\Rules\DateTimeFormattingRule',
            'LWP\Components\Rules\ConcatFormattingRule',
            'LWP\Components\Rules\CalcFormattingRule'
        ];
    }


    //

    public static function hasBuilder(): bool
    {

        return false;
    }
}
