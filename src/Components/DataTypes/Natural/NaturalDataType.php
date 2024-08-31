<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural;

use LWP\Components\DataTypes\DataType;

abstract class NaturalDataType extends DataType
{
    //

    abstract public static function getPhpVariableTypeEquivalent(): string;

}
