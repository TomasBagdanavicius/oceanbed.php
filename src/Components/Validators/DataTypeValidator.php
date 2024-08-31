<?php

declare(strict_types=1);

namespace LWP\Components\Validators;

use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\DataTypes\DataTypeFactory;

class DataTypeValidator extends Validator
{
    public function __construct(
        string|array $value,
    ) {

        parent::__construct($value);
    }


    // Validates the provided data type(s).

    public function validate(): true
    {

        if ($diff = array_diff((array)$this->value, DataTypeFactory::getDataTypeList())) {
            throw new NotFoundException(sprintf(
                "Unrecognized data type(s): %s.",
                "\"" . implode('", "', $diff) . "\""
            ));
        }

        return true;
    }
}
