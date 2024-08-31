<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class GenericViolation extends Violation
{
    public function __construct()
    {

        parent::__construct(constraint_value: true, value: false);
    }


    // Generic error message format.

    public function getErrorMessageFormat(): string
    {

        return "There has been an error.";
    }


    // Gets formatted error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: $this->getErrorMessageFormat());
    }
}
