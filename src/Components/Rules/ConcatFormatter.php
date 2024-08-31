<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class ConcatFormatter implements FormatterInterface
{
    public function __construct(
        public readonly ConcatFormattingRule $formatting_rule
    ) {

    }


    // Format by the given formatting rule options

    public function format(array $values): string
    {

        return implode($this->formatting_rule->getSeparator(), $values);
    }


    //

    public function canFormat(mixed $value): bool
    {

        return is_array($value);
    }
}
