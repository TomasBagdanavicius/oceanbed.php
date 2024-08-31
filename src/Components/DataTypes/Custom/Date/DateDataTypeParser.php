<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Date;

class DateDataTypeParser
{
    public function __construct(
        private DateDataTypeValueContainer $value_container,
    ) {

    }


    //

    public function getYear(): int
    {

        return $this->value_container->year;
    }


    //

    public function getMonth(): int
    {

        return $this->value_container->month;
    }


    //

    public function getDay(): int
    {

        return $this->value_container->day;
    }


    //

    public function getFormatted(): string
    {

        return sprintf(
            '%d-%s-%s',
            $this->value_container->year,
            str_pad((string)$this->value_container->month, 2, '0', STR_PAD_LEFT),
            str_pad((string)$this->value_container->day, 2, '0', STR_PAD_LEFT)
        );
    }
}
