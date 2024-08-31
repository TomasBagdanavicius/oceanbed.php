<?php

declare(strict_types=1);

namespace LWP\Components\Validators;

use LWP\Components\DataTypes\Custom\DateTime\Exceptions\InvalidDateTimeException;

class DateTimeValidator extends Validator
{
    public const DEFAULT_FORMAT = 'Y-m-d H:i:s';


    public function __construct(
        string $value
    ) {

        parent::__construct($value);
    }


    //

    public function validate(?string $format = null): bool
    {

        if (!$format) {

            try {
                // It seems that this throws warning messages as well, not just error messages. For instance, if higher number than 12 is used for a month, in some of the date time functions it can be treated as a warning level issue.
                $date_time = new \DateTime($this->value);
            } catch (\Exception $exception) {
                throw new InvalidDateTimeException(
                    "Date-time \"$this->value\" is invalid: {$exception->getMessage()}"
                );
            }

            return true;

        } else {

            return self::validateDateFormat($this->value, $format);
        }
    }


    // Validates a date-time string against a given format.

    public static function validateDateFormat(string $date_time_str, string $format = self::DEFAULT_FORMAT): bool
    {

        // It seems that this function doesn't throw exceptions.
        $date_time = \DateTime::createFromFormat($format, $date_time_str);
        $warnings_and_errors = \DateTime::getLastErrors();

        # This might be the same as doing `!$date_time`.
        if (
            // Not false and array is not empty.
            $warnings_and_errors
            && ($warnings_and_errors['warning_count'] !== 0 || $warnings_and_errors['error_count'] !== 0)
        ) {

            $all_errors = [];

            if (!empty($warnings_and_errors['warnings'])) {
                $all_errors = $warnings_and_errors['warnings'];
            }

            if (!empty($warnings_and_errors['errors'])) {
                $all_errors = [...$all_errors, ...$warnings_and_errors['errors']];
            }

            throw new InvalidDateTimeException(sprintf(
                "Date-time \"$date_time_str\" does not match given \"$format\" format: %s",
                implode('; ', array_unique($all_errors))
            ));
        }

        /* Assuming that "createFromFormat" checks both - date string and format
        - and there is no need to format the date and compare the result with
        the date string. */
        return true;
    }
}
