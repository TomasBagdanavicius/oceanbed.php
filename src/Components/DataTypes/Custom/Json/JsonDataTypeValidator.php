<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Json;

use LWP\Components\DataTypes\DataTypeValidator;

class JsonDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value
    ) {

    }


    //

    public function validate(): bool
    {

        if (in_array($this->value, ['null', 'false', '0', '""', '[]'])) {
            return true;
        }

        #todo (8.3): replace with `json_validate()`
        try {
            json_decode($this->value, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }
}
