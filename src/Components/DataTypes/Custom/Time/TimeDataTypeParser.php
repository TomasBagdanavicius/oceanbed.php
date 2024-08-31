<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Time;

class TimeDataTypeParser
{
    public function __construct(
        private TimeDataTypeValueContainer $value_container,
    ) {

    }


    //

    public function getHours(): int
    {

        return $this->value_container->hours;
    }


    //

    public function getMinutes(): int
    {

        return $this->value_container->minutes;
    }


    //

    public function getSeconds(): int
    {

        return $this->value_container->seconds;
    }


    //

    public function getFormatted(): string
    {

        return sprintf(
            '%s:%s:%s',
            str_pad((string)$this->value_container->hours, 2, '0', STR_PAD_LEFT),
            str_pad((string)$this->value_container->minutes, 2, '0', STR_PAD_LEFT),
            str_pad((string)$this->value_container->seconds, 2, '0', STR_PAD_LEFT)
        );
    }
}
