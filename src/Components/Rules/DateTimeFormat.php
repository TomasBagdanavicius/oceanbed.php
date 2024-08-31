<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Common\String\Str;
use LWP\Common\String\EnclosedCharsIterator;
use LWP\Components\Rules\Exceptions\DateTimeFormatNegotiationException;

class DateTimeFormat
{
    public function __construct(
        public DateTimeFormattingRule $date_time_formatting_rule,
        public DateTimeFormatMapInterface $map
    ) {

    }


    // Converts the format taken from the formatting rule to a new based on the provided map class.

    public function getFormat(): string
    {

        $format = $this->date_time_formatting_rule->getFormat();
        $enclosed_chars_iterator = self::parseFormat($format);
        $segments = [];
        $full_map = $this->map->getFullMap();
        $full_map_keys = array_keys($full_map);

        foreach ($enclosed_chars_iterator as $key => $subject) {

            // A segment with magic letters/specifiers.
            if (!$enclosed_chars_iterator->hasEnclosingChars()) {

                [$subject_result, $reserved_intervals] = Str::replaceOnce($full_map_keys, $full_map, $subject, return_reserved_intervals: true);

                $all_reserved_intervals = new \SplFixedArray(strlen($subject_result));

                foreach ($reserved_intervals as $intervals) {
                    $all_reserved_intervals->offsetSet($intervals[0], $intervals[0]);
                    $all_reserved_intervals->offsetSet($intervals[1], $intervals[1]);
                }

                $size = $all_reserved_intervals->getSize();

                for ($i = 0; $i < $size; $i++) {

                    if (!$all_reserved_intervals->offsetExists($i) && DateTimeFormatEnum::tryFrom($subject_result[$i])) {

                        throw new DateTimeFormatNegotiationException(sprintf(
                            "Could not replace element \"%s\" at index position %d in format \"%s\".",
                            $subject_result[$i],
                            $i,
                            $format
                        ));
                    }
                }

                $segments[] = $subject_result;

                // A regular text segment where letters should not be intepreted as magic letters/specifiers.
            } else {

                // Assumption is that a single char should be trimmed off on each side.
                $segments[] = $this->map::escape(substr(substr($subject, 1), 0, -1));
            }
        }

        return implode($segments);
    }


    // Parses a custom format, where regular text is enclosed in curly brackets, rather than escaping each letter with a backslash.

    public static function parseFormat(string $format, bool $return_as_array = false): EnclosedCharsIterator|array
    {

        $enclosed_chars_iterator = new EnclosedCharsIterator($format, [
            '{' => ['}', true],
        ]);

        return (!$return_as_array)
            ? $enclosed_chars_iterator
            : iterator_to_array($enclosed_chars_iterator);
    }


    //

    public static function customFormatToStandardFormat(string $custom_format): string
    {

        $enclosed_chars_iterator = self::parseFormat($custom_format);
        $result = '';

        foreach ($enclosed_chars_iterator as $key => $subject) {

            // A segment with magic letters/specifiers.
            if (!$enclosed_chars_iterator->hasEnclosingChars()) {

                $result .= $subject;

                // A regular text segment where letters should not be intepreted as magic letters/specifiers.
            } else {

                // Trim off curly brackets and make sure there is some text.
                if ($text = substr(substr($subject, 1), 0, -1)) {

                    $text_len = strlen($text);

                    for ($i = 0; $i < $text_len; $i++) {
                        $result .= ('\\' . $text[$i]);
                    }
                }
            }
        }

        return $result;
    }
}
