<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\FilePath;

use LWP\Components\DataTypes\DataTypeValidator;

class FilePathDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value
    ) {

    }


    //

    public function validate(): bool
    {

        return file_exists($this->value);
    }
}
