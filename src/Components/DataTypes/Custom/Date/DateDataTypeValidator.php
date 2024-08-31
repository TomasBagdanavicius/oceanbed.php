<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Date;

use LWP\Components\DataTypes\DataTypeValidator;

class DateDataTypeValidator extends DataTypeValidator
{
    public function __construct(
        public mixed $value
    ) {

    }


    //

    public function validate(): bool
    {

        // Apparently, this function does not throw errors.
        // Leading exclamation mark is used to exclude current system time.
        $datetime = \DateTime::createFromFormat('!Y-n-j', $this->value);
        $last_errors = \DateTime::getLastErrors();

        return (!$last_errors || (!$last_errors['warning_count'] && !$last_errors['error_count']));
    }
}
