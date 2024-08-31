<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class EqualToViolation extends Violation
{
    public function __construct(
        mixed $primary_value,
        mixed $secondary_value,
    ) {

        parent::__construct($primary_value, $secondary_value);
    }


    // Gets error message string format (usually to be used with "sprintf").

    public function getErrorMessageFormat(): string
    {

        return "Primary value must not be equal to secondary value, both have value \"%s\".";
    }


    // Gets formatted error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->constraint_value));
    }
}
