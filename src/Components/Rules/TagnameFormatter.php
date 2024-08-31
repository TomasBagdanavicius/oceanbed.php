<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Common\String\Format;

class TagnameFormatter implements FormatterInterface
{
    public function __construct(
        public readonly TagnameFormattingRule $formatting_rule
    ) {

    }


    // Format by the given formatting rule options.

    public function format(string $value): string
    {

        return Format::tagname(
            str: $value,
            limit: $this->formatting_rule->getMaxLength(),
            separator: $this->formatting_rule->getSeparator()
        );
    }


    //

    public function canFormat(mixed $value): bool
    {

        return is_string($value);
    }
}
