<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\DateTime;

use LWP\Components\DataTypes\DataTypeValidator;
use LWP\Components\Rules\DateTimeFormattingRule;
use LWP\Components\Validators\DateTimeValidator;
use LWP\Components\DataTypes\Custom\DateTime\Exceptions\InvalidDateTimeException;

class DateTimeDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value,
        public ?DateTimeFormattingRule $date_time_formatting_rule = null
    ) {

    }


    //

    public function validate(): bool
    {

        try {
            // Must be standard format (eg. with backslash escape characters).
            return (new DateTimeValidator($this->value))->validate($this->date_time_formatting_rule?->getStandardFormat());
        } catch (InvalidDateTimeException) {
            return false;
        }
    }
}
