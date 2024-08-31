<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\TimeDuration;

class TimeDurationDataTypeParser
{
    private \DateInterval $interval;


    public function __construct(
        public readonly TimeDurationDataTypeValueContainer $time_duration_value,
    ) {

        if (!($time_duration_value->validator instanceof TimeDurationDataTypeValidator)) {
            throw new \TypeError(sprintf(
                "%s expects provided \$time_duration_value to contain validator property value",
                TimeDurationDataTypeParser::class
            ));
        }

        $this->interval = $time_duration_value->validator->getLastDateInterval();
    }


    //

    public function getYears(): int
    {

        return (int)$this->interval->format('%y');
    }


    //

    public function getMonths(): int
    {

        return (int)$this->interval->format('%m');
    }


    //

    public function getDays(): int
    {

        return (int)$this->interval->format('%d');
    }


    //

    public function getHours(): int
    {

        return (int)$this->interval->format('%h');
    }


    //

    public function getMinutes(): int
    {

        return (int)$this->interval->format('%i');
    }


    //

    public function getSeconds(): int
    {

        return (int)$this->interval->format('%s');
    }


    //
    // See: https://www.php.net/manual/en/dateinterval.format.php#refsect1-dateinterval.format-parameters

    public function getByFormat(string $format): string
    {

        return $this->interval->format($format);
    }
}
