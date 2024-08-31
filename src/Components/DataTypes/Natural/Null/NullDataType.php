<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Null;

use LWP\Components\DataTypes\Natural\NaturalDataType;

class NullDataType extends NaturalDataType
{
    public const TYPE_NAME = 'null',
        TYPE_TITLE = 'NULL';


    //

    public static function getPhpVariableTypeEquivalent(): string
    {

        return 'null';
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
