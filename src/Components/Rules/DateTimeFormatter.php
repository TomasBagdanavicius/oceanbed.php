<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\Rules\DateTimeFormat;
use LWP\Common\Exceptions\FormatError;

class DateTimeFormatter implements FormatterInterface
{
    public function __construct(
        public readonly DateTimeFormattingRule $formatting_rule
    ) {

    }


    // Format by the given formatting rule options.

    public function format(string|int|DateTimeDataTypeValueContainer $value): string
    {

        $format = $this->formatting_rule->getFormat();
        $timestamp = match (true) {
            is_string($value) => strtotime($value),
            is_int($value) => $value,
            default => $value->getSize()
        };

        // Falsy comes from "strtotime"
        if ($timestamp === false) {
            throw new FormatError(sprintf("Value \"%s\" could not be converted to date time", $value));
        }

        // Supports custom formatting with text in curly brackets.
        $iterator = DateTimeFormat::parseFormat($format);
        $result = '';

        foreach ($iterator as $subject) {

            // A segment with magic letters/specifiers.
            if (!$iterator->hasEnclosingChars()) {
                $result .= date($subject, $timestamp);
                // A regular text segment where letters should not be intepreted as magic letters/specifiers.
            } else {
                $result .= substr(substr($subject, 1), 0, -1);
            }
        }

        return $result;
    }


    // Tells whether given value type can be formatted

    public function canFormat(mixed $value): bool
    {

        return (is_string($value) || is_integer($value) || $value instanceof DateTimeDataTypeValueContainer);
    }
}
