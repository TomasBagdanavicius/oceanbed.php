<?php

declare(strict_types=1);

namespace LWP\Common;

use LWP\Common\String\Format;

class DateTime
{
    public static $days_in_week = 7;
    public static $weekend_days = [6, 7];


    public static function getWorkingDays($weekend_days = null)
    {

        if (empty($weekend_days)) {
            $weekend_days = self::$weekend_days;
        }

        $week_range = range(1, self::$days_in_week);
        $working_days = array_diff($week_range, $weekend_days);

        return $working_days;
    }


    public static function getLeftoverRange($start_day, $end_day)
    {

        $start_day = new \DateTime($start_day);
        $end_day = new \DateTime($end_day);

        // number of days between start day and end day
        $days_count = $start_day->diff($end_day, true)->days;

        // day of week of start day
        $start_day_n = $start_day->format('N');

        // the number of leftover days that don't divide evenly into weeks
        $leftover = ($days_count % self::$days_in_week);

        // add leftover days to start day number
        $period_end = ($start_day_n + $leftover);

        // range cannot exceed number of days in week
        $range = range($start_day_n, min(self::$days_in_week, $period_end));

        if ($period_end > self::$days_in_week) {

            // add another range which starts from day 1
            $range = array_merge(
                $range,
                range(1, ($period_end - self::$days_in_week))
            );
        }

        return [
            $start_day,
            $end_day,
            $days_count,
            $range
        ];
    }


    public static function countBusinessDays($start_day, $end_day, $holidays = [], $weekend_days = null)
    {

        if (empty($weekend_days)) {
            $weekend_days = self::$weekend_days;
        }

        list($start_day, $end_date, $days_count, $range) = self::getLeftoverRange($start_day, $end_day);

        // if day of week was found in leftover range, add one point
        $count_weekend_days = (intval($days_count / self::$days_in_week) * 2);

        foreach ($weekend_days as $weekend_day) {

            if (in_array($weekend_day, $range)) {
                $count_weekend_days++;
            }
        }

        // - +1, because Datetime::diff returns difference, not inclusive count of days
        $count = ($days_count + 1 - $count_weekend_days);

        if (!empty($holidays)) {

            $start_day_timestamp = $start_day->getTimestamp();
            $end_date_timestamp = $end_date->getTimestamp();

            foreach ($holidays as $holiday) {

                $time_stamp = strtotime($holiday);
                $day_of_week = date("N", $time_stamp);

                if (
                    $start_day_timestamp <= $time_stamp
                    && $time_stamp <= $end_date_timestamp
                    && !in_array($day_of_week, $weekend_days)
                ) {
                    $count--;
                }
            }
        }

        return $count;
    }


    public static function countDaysOfWeek($start_day, $end_day, $day_number = 7)
    {

        list($start_day, $end_date, $days_count, $range) = self::getLeftoverRange($start_day, $end_day);

        // if day of week was found in leftover range, add one point
        $count = (
            intval($days_count / self::$days_in_week)
            + in_array($day_number, $range)
        );

        return $count;
    }


    // Check if it's a valid timestamp

    public static function isValidTimeStamp(mixed $timestamp): bool
    {

        return (
            is_numeric($timestamp)
            && (string)(int)$timestamp == $timestamp
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX)
        );
    }


    // Measures given time before the present

    public static function timeElapsedString(string $datetime, bool $full = false): string
    {

        $now = new \DateTime();
        $ago = new \DateTime($datetime);

        if ($now->getTimestamp() === $ago->getTimestamp()) {
            return 'now';
        }

        $diff = $now->diff($ago);

        return self::diffToString($diff, $full);
    }


    // Converts PHP's DateInterval difference object to a string expression

    public static function diffToString(\DateInterval $diff, bool $full = false, ?string $suffix = 'ago'): string
    {

        $extra_props = [
            'w' => floor($diff->d / 7),
        ];
        $diff->d -= ($extra_props['w'] * 7);
        $strings = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second'
        ];

        foreach ($strings as $k => &$v) {

            if (isset($diff->$k) && $diff->$k) {
                $v = $diff->$k . ' ' . Format::getCasualSingularOrPlural((int)$diff->$k, $v);
            } elseif (isset($extra_props[$k]) && $extra_props[$k]) {
                $v = $extra_props[$k] . ' ' . Format::getCasualSingularOrPlural((int)$extra_props[$k], $v);
            } else {
                unset($strings[$k]);
            }
        }

        if (!$full) {
            $strings = array_slice($strings, 0, 1);
        }

        return $strings
            ? implode(' ', $strings) . (($suffix) ? ' ' . $suffix : '')
            : 'just now';
    }


    // Converts seconds to a readable string expression

    public static function frequencyReadable(int $seconds): string
    {

        $now = new \DateTime();
        $ago = clone ($now);
        $ago->modify('+' . $seconds . ' seconds');

        return self::diffToString($now->diff($ago), true, null);
    }
}
