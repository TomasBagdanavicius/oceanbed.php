<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Common\Exceptions\FormatError;

class CalcFormatter implements FormatterInterface
{
    public function __construct(
        public readonly CalcFormattingRule $formatting_rule
    ) {

    }


    // Format by the given formatting rule options.

    public function format(string $value): string
    {

        $subject = $this->formatting_rule->getSubject();

        if ($subject === 'age') {
            $timestamp = strtotime($value);
            if ($timestamp === false) {
                throw new FormatError("Cannot format value to age");
            }
            // The "@" sign is used to create a DateTime object from a Unix timestamp
            $past_datetime = new \DateTime("@$timestamp");
            return (string)(new \DateTime())->diff($past_datetime)->y;
        }
    }


    // Tells whether given value type can be formatted

    public function canFormat(mixed $value): bool
    {

        return (is_string($value));
    }
}
