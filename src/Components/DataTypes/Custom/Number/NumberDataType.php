<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Components\DataTypes\Custom\CustomDataType;

class NumberDataType extends CustomDataType
{
    public const TYPE_NAME = 'number';
    public const TYPE_TITLE = 'Number';


    //

    public static function getSupportedConstraintClassNameList(): ?array
    {

        return [
            'LWP\Components\Constraints\MinSizeConstraint',
            'LWP\Components\Constraints\MaxSizeConstraint',
            'LWP\Components\Constraints\InDatasetConstraint',
            'LWP\Components\Constraints\NotInDatasetConstraint',
        ];
    }


    //

    public static function getSupportedDefinitionList(): ?array
    {

        return [
            // Constraints
            'min',
            'max',
            // Formatting Rules
            'pre_number_format',
            'number_format',
        ];
    }


    //

    public static function getSupportedFormattingRuleList(): ?array
    {

        return [
            'LWP\Components\Rules\NumberFormattingRule',
        ];
    }


    //

    public static function hasBuilder(): bool
    {

        return true;
    }


    //

    public static function getBuilderClassObject(): NumberDataTypeBuilder
    {

        return new (self::getBuilderClassName())();
    }
}
