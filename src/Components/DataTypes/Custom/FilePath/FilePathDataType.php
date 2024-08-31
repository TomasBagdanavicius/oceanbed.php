<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\FilePath;

use LWP\Components\DataTypes\Custom\CustomDataType;

class FilePathDataType extends CustomDataType
{
    public const TYPE_NAME = 'file_path';
    public const TYPE_TITLE = 'File Path';


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

    public static function hasBuilder(): bool
    {

        return false;
    }
}
