<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class SerializeFormatter implements FormatterInterface
{
    public function __construct(
        public readonly SerializeFormattingRule $formatting_rule
    ) {

    }


    // Format by the given formatting rule options

    public function format(mixed $value): string
    {

        return serialize($value);
    }


    //

    public function canFormat(mixed $value): true
    {

        // Can format any value
        return true;
    }
}
