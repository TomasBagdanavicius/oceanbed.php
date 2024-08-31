<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Array;

use LWP\Components\DataTypes\Natural\NaturalDataType;

class ArrayDataType extends NaturalDataType
{
    public const TYPE_NAME = 'array';
    public const TYPE_TITLE = 'Array';


    //

    public static function getPhpVariableTypeEquivalent(): string
    {

        return 'array';
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


    //

    public static function testEmpty(mixed $value): bool
    {

        return ($value === []);
    }
}
