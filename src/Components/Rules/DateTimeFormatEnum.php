<?php

/* This works better than constants inside DateTimeFormat, because (1) easier to manipulate the list (2) the entire list is isolated and complete. */

declare(strict_types=1);

namespace LWP\Components\Rules;

enum DateTimeFormatEnum: string
{
    // Day
    case DAY_NUM_LEADING_ZERO = 'd'; // 01 to 31.
    case DAY_NUM = 'j'; // 1 to 31.
    case DAY_TEXT_SHORT = 'D'; // Mon through Sun.
    case DAY_OF_WEEK_TEXT_FULL = 'l'; // Sunday through Saturday.
    case DAY_OF_WEEK_ISO_8601 = 'N'; // 1 (for Monday) through 7 (for Sunday).
    case DAY_OF_WEEK_NUM_ZERO_BASED = 'w'; // 0 (for Sunday) through 6 (for Saturday).
    case DAY_OF_YEAR_ZERO_BASED = 'z'; // 0 through 365.
    case DAY_EN_ORDINAL_SUFFIX = 'S'; // st, nd, rd or th.
    // Week
    case WEEK_NUM_ISO_8601 = 'W'; // Example: 42 (the 42nd week in the year).
    // Month
    case MONTH_NUM_LEADING_ZERO = 'm'; // 01 through 12.
    case MONTH_NUM = 'n'; // 1 through 12.
    case MONTH_TEXT_FULL = 'F'; // January through December.
    case MONTH_TEXT_SHORT = 'M'; // Jan through Dec.
    case MONTH_NUM_OF_DAYS = 't'; // 28 through 31.
    // Year
    case YEAR_IS_LEAP = 'L'; // 1 if it is a leap year, 0 otherwise.
    case YEAR_NUM_ISO_8601 = 'o'; // Examples: 1999 or 2003.
    case YEAR_NUM = 'Y'; // Examples: 1999 or 2003.
    case YEAR_NUM_TWO_DIGIT = 'y'; // Examples: 99 or 03.
    // Time
    case TIME_MERIDIEM_LOWERCASE = 'a'; // am or pm.
    case TIME_MERIDIEM_UPPERCASE = 'A'; // AM or PM.
    case TIME_SWATCH_INTERNET = 'B'; // 000 through 999
    case TIME_TWELVE_FORMAT = 'g'; // 1 through 12.
    case TIME_TWENTY_FOUR_FORMAT = 'G'; // 0 through 23.
    case TIME_TWELVE_FORMAT_LEADING_ZERO = 'h'; // 01 through 12.
    case TIME_TWENTY_FOUR_FORMAT_LEADING_ZERO = 'H'; // 00 through 23.
    case TIME_MINUTES = 'i'; // 00 to 59.
    case TIME_SECONDS = 's'; // 00 through 59.
    case TIME_MICROSECONDS = 'u'; // Example: 654321.
    case TIME_MILLISECONDS = 'v'; // Example: 654.
    // Timezone
    case TIMEZONE_IDENTIFIER = 'e'; // Examples: UTC, GMT, Atlantic/Azores.
    // Full Date/Time
    case FULL_ISO_8601 = 'c'; // Example: 2004-02-12T15:19:21+00:00.
    case FULL_RFC_2822 = 'r'; // Example: Thu, 21 Dec 2000 16:01:07 +0200.
    case FULL_TIMESTAMP = 'U'; // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT).

}
