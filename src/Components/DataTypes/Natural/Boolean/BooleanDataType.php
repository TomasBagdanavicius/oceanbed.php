<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Boolean;

use LWP\Components\DataTypes\Natural\NaturalDataType;

class BooleanDataType extends NaturalDataType
{
    public const TYPE_NAME = 'boolean',
        TYPE_TITLE = 'Boolean';


    //

    public static function getPhpVariableTypeEquivalent(): string
    {

        return 'boolean';
    }


    //

    public static function getSupportedConstraintClassNameList(): ?array
    {

        return null;
    }


    //

    public static function getSupportedFormattingRuleList(): ?array
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
