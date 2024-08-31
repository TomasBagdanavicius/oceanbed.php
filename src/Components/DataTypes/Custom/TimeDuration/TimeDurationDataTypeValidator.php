<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\TimeDuration;

use LWP\Components\DataTypes\DataTypeValidator;

class TimeDurationDataTypeValidator extends DataTypeValidator
{
    private \DateInterval $last_date_interval;


    public function __construct(
        public mixed $value,
    ) {

    }


    // Gets the last "DateInterval" that was used to perform validation.

    public function getLastDateInterval(): ?\DateInterval
    {

        return ($this->last_date_interval ?? null);
    }


    //

    public function validate(): bool
    {

        try {

            // Validation method is to try and create a date interval object.
            $this->last_date_interval = new \DateInterval($this->value);
            return true;

            // Throws a standard exception.
        } catch (\Exception) {

            return false;
        }
    }
}
