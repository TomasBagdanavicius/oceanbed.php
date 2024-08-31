<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Common\String\Str;

class StringTrimFormatter implements FormatterInterface
{
    public function __construct(
        private StringTrimFormattingRule $formatting_rule,
    ) {

    }


    // Format by the given formatting rule options.
    // Multibyte safe.

    public function format(string $value): string
    {

        $mask = $this->formatting_rule->getMask();

        if ($mask == '') {
            return $value;
        }

        $mask_as = $this->formatting_rule->getMaskAs();
        $repeatable = $this->formatting_rule->getRepeatable();
        $side = $this->formatting_rule->getSide();

        if ($mask_as === StringTrimFormattingRule::MASK_AS_CHARS) {
            $value = Str::mbTrim($value, $mask, $side, $repeatable);
        } elseif ($mask_as === StringTrimFormattingRule::MASK_AS_SUBSTRING) {
            $value = Str::mbTrimSubstring($value, $mask, $side, $repeatable);
        }

        return $value;
    }


    //

    public function canFormat(mixed $value): bool
    {

        return is_string($value);
    }
}
