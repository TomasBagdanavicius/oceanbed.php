<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Components\DataTypes\Custom\DateTime\DateTimeDataTypeValueContainer;
use LWP\Components\Rules\DateTimeFormat;

class DateTimeFormattingRule extends FormattingRule
{
    public function __construct(
        array $options = [],
    ) {

        parent::__construct($options);
    }


    // Gets default value for each option.

    public static function getOptionDefaultValues(): array
    {

        return [
            'format' => DateTimeDataTypeValueContainer::DEFAULT_FORMAT,
        ];
    }


    // Gets supported option list.

    public static function getSupportedOptions(): array
    {

        return [
            'format',
        ];
    }


    // Gets option definitions.

    public static function getOptionDefinitions(): array
    {

        return [
            'format' => [
                'type' => 'string',
                'default' => DateTimeDataTypeValueContainer::DEFAULT_FORMAT,
                'allow_empty' => false,
                'description' => "Date-time format.",
            ],
        ];
    }


    // Gets the format.
    /* Generally, this can be either custom or standard, but custom is the default. */

    public function getFormat(): string
    {

        return $this->options->format;
    }


    // Gets the standard format representation (eg. with backslash escape characters).

    public function getStandardFormat(): string
    {

        return DateTimeFormat::customFormatToStandardFormat($this->getFormat());
    }
}
