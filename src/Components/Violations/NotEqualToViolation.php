<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class NotEqualToViolation extends Violation
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

        return "Primary value ({$this->constraint_value}) is not equal to secondary value ({$this->value}).";
    }


    // Gets formatted error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: $this->getErrorMessageFormat());
    }


    // Offers possible violation correction opportunities or options.

    public function getCorrectionOpportunities(): ?array
    {

        return [
            // To match the primary value.
            $primary_value,
        ];
    }
}
