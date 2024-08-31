<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

use LWP\Components\DataTypes\Natural\NaturalDataType;

class StringDataType extends NaturalDataType
{
    public const TYPE_NAME = 'string';
    public const TYPE_TITLE = 'String';


    //

    public static function getPhpVariableTypeEquivalent(): string
    {

        return 'string';
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
            'LWP\Components\Rules\StringTrimFormattingRule',
            'LWP\Components\Rules\TagnameFormattingRule',
            'LWP\Components\Rules\ConcatFormattingRule',
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
            // Formatting Rules
            'pre_trim',
            'trim',
        ];
    }


    //

    public static function hasBuilder(): bool
    {

        return true;
    }


    //

    public static function getBuilderClassObject(): StringDataTypeBuilder
    {

        return new (self::getBuilderClassName())();
    }


    //

    public static function testEmpty(mixed $value): bool
    {

        return ($value === '');
    }
}
