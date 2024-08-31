<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Integer;

use LWP\Components\DataTypes\Natural\NaturalDataType;
use LWP\Components\DataTypes\DataTypeBuilder;

class IntegerDataType extends NaturalDataType
{
    public const TYPE_NAME = 'integer';
    public const TYPE_TITLE = 'Integer';


    //

    public static function getPhpVariableTypeEquivalent(): string
    {

        return 'integer';
    }


    //

    public static function getSupportedConstraintClassNameList(): ?array
    {

        return [
            'LWP\Components\Constraints\InSetConstraint',
            'LWP\Components\Constraints\NotInSetConstraint',
            'LWP\Components\Constraints\MinSizeConstraint',
            'LWP\Components\Constraints\MaxSizeConstraint',
            'LWP\Components\Constraints\InDatasetConstraint',
            'LWP\Components\Constraints\NotInDatasetConstraint',
        ];
    }


    //

    public static function getSupportedFormattingRuleList(): ?array
    {

        return [
            'LWP\Components\Rules\ConcatFormattingRule',
            #review: integer timestamp to date
            'LWP\Components\Rules\DateTimeFormattingRule'
        ];
    }


    //

    public static function getSupportedDefinitionList(): ?array
    {

        return [
            // Constraints
            'in_set',
            'not_in_set',
            'min',
            'max',
        ];
    }


    //

    public static function hasBuilder(): bool
    {

        return true;
    }


    //

    public static function getBuilderClassObject(): DataTypeBuilder
    {

        return new (self::getBuilderClassName())();
    }


    //

    public static function testEmpty(mixed $value): bool
    {

        return ($value === 0);
    }
}
