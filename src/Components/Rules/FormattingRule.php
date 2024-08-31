<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Common\Common;
use LWP\Common\String\Str;
use LWP\Common\OptionManager;

abstract class FormattingRule
{
    public readonly OptionManager $options;


    public function __construct(
        public readonly array $custom_options = [],
    ) {

        $this->options = new OptionManager(
            $custom_options,
            static::getOptionDefaultValues(),
            static::getSupportedOptions()
        );
    }


    // Provides supported option list.

    abstract public static function getSupportedOptions(): array;


    // Provides default values for each supported option.

    abstract public static function getOptionDefaultValues(): array;


    // Provides option definition collection set as an array.

    abstract public static function getOptionDefinitions(): array;


    // Gets the formatter object instance.

    public function getFormatter(): FormatterInterface
    {

        return new (__NAMESPACE__
            . '\\' . Str::rtrimSubstring(str_replace(__NAMESPACE__ . '\\', '', static::class), 'FormattingRule')
            . 'Formatter')($this);
    }


    // Determines if given formatting rule matches the given static formatting rule by comparing their options.

    public function matches(self $formatting_rule): bool
    {

        // Given formatting rule must match caller's class.
        if (!($formatting_rule instanceof static)) {
            Common::throwTypeError(1, __FUNCTION__, static::class, $formatting_rule::class);
        }

        return ($this->options->toArray() == $formatting_rule->options->toArray());
    }
}
