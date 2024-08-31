<?php

declare(strict_types=1);

namespace LWP\Components\Validators;

use LWP\Common\Exceptions\NotFoundException;
use LWP\Components\DataTypes\Natural\NaturalDataTypeFactory;

class VariableTypeValidator extends Validator
{
    public function __construct(
        string|array $value,
    ) {

        parent::__construct($value);
    }


    // Validates the provided variable type(s).

    public function validate(): true
    {

        if ($diff = array_diff((array)$this->value, NaturalDataTypeFactory::getVariableTypeList())) {
            throw new NotFoundException(sprintf(
                "Unrecognized variable type(s): %s.",
                "\"" . implode('", "', $diff) . "\""
            ));
        }

        return true;
    }
}
