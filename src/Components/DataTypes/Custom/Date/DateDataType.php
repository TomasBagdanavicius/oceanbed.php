<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Date;

use LWP\Components\DataTypes\Custom\CustomDataType;

class DateDataType extends CustomDataType
{
    public const TYPE_NAME = 'date';
    public const TYPE_TITLE = 'Date';


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
            'LWP\Components\Rules\ConcatFormattingRule'
        ];
    }


    //

    public static function hasBuilder(): bool
    {

        return false;
    }
}
