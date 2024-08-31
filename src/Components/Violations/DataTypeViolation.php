<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

use LWP\Components\Validators\DataTypeValidator;

class DataTypeViolation extends InSetViolation
{
    public function __construct(
        string|array $set,
        string|array $value,
        string|array $missing_value,
    ) {

        // Will validate if the set of data types intersect with the list of official data types.
        (new DataTypeValidator($set))->validate();

        parent::__construct((array)$set, $value, $missing_value);
    }


    // Gets error message text template.

    public function getErrorMessageFormat(): string
    {

        return "Disallowed data type(s): %s. Must be one of: %s.";
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf(
                static::getErrorMessageFormat(),
                self::getArrayValuesAsQuotedStrings($this->missing_values),
                self::getArrayValuesAsQuotedStrings($this->constraint_value)
            )
        );
    }
}
