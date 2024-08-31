<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\DateTime;

class DateTimeDataTypeParser extends \DateTime
{
    public function __construct(
        public readonly DateTimeDataTypeValueContainer $datetime_value,
        public readonly ?\DateTimeZone $timezone = null,
    ) {

        parent::__construct($datetime_value->__toString(), $timezone);
    }


    //

    public function __toString(): string
    {

        return $this->datetime_value->__toString();
    }
}
