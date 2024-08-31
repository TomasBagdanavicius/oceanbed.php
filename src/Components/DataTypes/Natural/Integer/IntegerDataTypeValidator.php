<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Natural\Integer;

use LWP\Components\DataTypes\DataTypeValidator;

class IntegerDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value,
    ) {

    }


    //

    public function validate(): bool
    {

        return is_int($this->value);
    }
}
