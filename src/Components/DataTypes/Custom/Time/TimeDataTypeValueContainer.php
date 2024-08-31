<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Time;

use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\DataTypes\Exceptions\DataTypeError;

class TimeDataTypeValueContainer extends DateTimeDataTypeValueContainer
{
    public const DEFAULT_FORMAT = 'H:i:s';


    public function __construct(
        public readonly int $hours,
        public readonly int $minutes,
        public readonly int $seconds,
    ) {

        // PHP does not support minute and second formats without leading zeros and require it to be a 2-digit number.
        $minutes = str_pad((string)$minutes, 2, '0', STR_PAD_LEFT);
        $seconds = str_pad((string)$seconds, 2, '0', STR_PAD_LEFT);

        // Apparently, this function does not throw errors.
        // Leading exclamation mark is used to exclude current system time.
        $time = \DateTime::createFromFormat('G:i:s', ($hours . ':' . $minutes . ':' . $seconds));
        $last_errors = \DateTime::getLastErrors();

        if ($last_errors && ($last_errors['warning_count'] || $last_errors['error_count'])) {
            throw new DataTypeError(sprintf("Given hour (%d), minute (%d), and second (%d) values do not constitute a valid time.", $hours, $minutes, $seconds));
        }

        parent::__construct($time->format('Y-m-d H:i:s'));
    }


    //

    public function getHours(): string
    {

        return $this->format('H');
    }


    //

    public function getMinutes(): string
    {

        return $this->format('i');
    }


    //

    public function getSeconds(): string
    {

        return $this->format('s');
    }


    //

    public function getValue(): string
    {

        return $this->__toString();
    }


    //

    public static function from(int|string|\DateTime $time): self
    {

        $datetime = parent::from($time);

        return new self(
            intval($datetime->format('G')),
            intval($datetime->format('i')),
            intval($datetime->format('s')),
        );
    }
}
