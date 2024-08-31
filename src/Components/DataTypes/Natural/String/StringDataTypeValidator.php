<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\String;

use LWP\Components\DataTypes\DataTypeValidator;

class StringDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value,
    ) {

    }


    //

    public function validate(): bool
    {

        return is_string($this->value);
    }
}
