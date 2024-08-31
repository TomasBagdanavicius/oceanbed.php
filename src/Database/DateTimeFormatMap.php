<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Components\Rules\DateTimeFormatMapInterface;
use LWP\Components\Rules\DateTimeFormatEnum as DTFEnum;

class DateTimeFormatMap implements DateTimeFormatMapInterface
{
    public const ESCAPE_CHAR = '%';


    // Contains more complex cases, eg. concatenated format characters, replacements containing multi-specifiers, etc.

    public static function getPrimaryMap(): array
    {

        return [
            /* The order might be relevant. */
            (DTFEnum::DAY_NUM->value . DTFEnum::DAY_EN_ORDINAL_SUFFIX->value) => '%D', // Day of the month with English suffix (0th, 1st, 2nd, 3rd, …)
            (DTFEnum::TIME_TWELVE_FORMAT_LEADING_ZERO->value . ':' . DTFEnum::TIME_MINUTES->value . ':' . DTFEnum::TIME_SECONDS->value . ' ' . DTFEnum::TIME_MERIDIEM_UPPERCASE->value) => '%r', // Time, 12-hour (hh:mm:ss followed by AM or PM)
            (DTFEnum::TIME_TWENTY_FOUR_FORMAT_LEADING_ZERO->value . ':' . DTFEnum::TIME_MINUTES->value . ':' . DTFEnum::TIME_SECONDS->value) => '%T', // Time, 24-hour (hh:mm:ss)
            (DTFEnum::YEAR_NUM->value . DTFEnum::WEEK_NUM_ISO_8601->value) => '%Y%v',
            (DTFEnum::YEAR_NUM_ISO_8601->value . DTFEnum::WEEK_NUM_ISO_8601->value) => '%x%v', // Year for the week, where Monday is the first day of the week, numeric, four digits; used with %v. Week (01..53), where Monday is the first day of the week; WEEK() mode 3; used with %x.
        ];
    }


    // Simple cases where one specifier is replaced with another.

    public static function getSecondaryMap(): array
    {

        return [
            DTFEnum::DAY_TEXT_SHORT->value => '%a', // Abbreviated weekday name (Sun..Sat)
            DTFEnum::MONTH_TEXT_SHORT->value => '%b', // Abbreviated month name (Jan..Dec)
            DTFEnum::MONTH_NUM->value => '%c', // Month, numeric (0..12)
            DTFEnum::DAY_NUM_LEADING_ZERO->value => '%d', // Day of the month, numeric (00..31)
            DTFEnum::DAY_NUM->value => '%e', // Day of the month, numeric (0..31)
            DTFEnum::TIME_MICROSECONDS->value => '%f', // Microseconds (000000..999999)
            DTFEnum::TIME_TWENTY_FOUR_FORMAT_LEADING_ZERO->value => '%H', // Hour (00..23)
            DTFEnum::TIME_TWELVE_FORMAT_LEADING_ZERO->value => '%h', // Hour (01..12)
            DTFEnum::TIME_TWELVE_FORMAT_LEADING_ZERO->value => '%I', // Hour (01..12)
            DTFEnum::TIME_MINUTES->value => '%i', // Minutes, numeric (00..59)
            DTFEnum::DAY_OF_YEAR_ZERO_BASED->value => '%j', // Day of year (001..366)
            DTFEnum::TIME_TWENTY_FOUR_FORMAT->value => '%k', // Hour (0..23)
            DTFEnum::TIME_TWELVE_FORMAT->value => '%l', // Hour (1..12)
            DTFEnum::MONTH_TEXT_FULL->value => '%M', // Month name (January..December)
            DTFEnum::MONTH_NUM_LEADING_ZERO->value => '%m', // Month, numeric (00..12)
            DTFEnum::TIME_MERIDIEM_UPPERCASE->value => '%p', // AM or PM
            // There is no lowercase substitute, hence using uppercase.
            DTFEnum::TIME_MERIDIEM_LOWERCASE->value => '%p', // AM or PM
            DTFEnum::TIME_SECONDS->value => '%S', // Seconds (00..59)
            DTFEnum::TIME_SECONDS->value => '%s', // Seconds (00..59)
            DTFEnum::WEEK_NUM_ISO_8601->value => '%u', // Week (00..53), where Monday is the first day of the week; WEEK() mode 1
            DTFEnum::DAY_OF_WEEK_TEXT_FULL->value => '%W', // Weekday name (Sunday..Saturday)
            DTFEnum::DAY_OF_WEEK_NUM_ZERO_BASED->value => '%w', // Day of the week (0=Sunday..6=Saturday)
            DTFEnum::YEAR_NUM->value => '%Y', // Year, numeric, four digits
            DTFEnum::YEAR_NUM_TWO_DIGIT->value => '%y', // Year, numeric (two digits)
            /* Not implemented. Might not be supported.
            DTFEnum:: => '%U', // Week (00..53), where Sunday is the first day of the week; WEEK() mode 0
            DTFEnum:: => '%V', // Week (01..53), where Sunday is the first day of the week; WEEK() mode 2; used with %X
            DTFEnum:: => '%X', // Year for the week where Sunday is the first day of the week, numeric, four digits; used with %V
            DTFEnum:: => '%x', // x, for any “x” not listed above
            */
        ];
    }


    // Primary map merged with the secondary one.

    public static function getFullMap(): array
    {

        return (self::getPrimaryMap() + self::getSecondaryMap());
    }


    // Handles regular text escaping, where specifiers are not used.

    public static function escape(string $str): string
    {

        return str_replace(self::ESCAPE_CHAR, str_repeat(self::ESCAPE_CHAR, 2), $str);
    }
}
