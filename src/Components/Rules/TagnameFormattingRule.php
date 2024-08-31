<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class TagnameFormattingRule extends FormattingRule
{
    public function __construct(
        array $options = []
    ) {

        parent::__construct($options);
    }


    // Provides supported option list.

    public static function getSupportedOptions(): array
    {

        return [
            'separator',
            'max_length',
        ];
    }


    // Provides default values for each supported option.

    public static function getOptionDefaultValues(): array
    {

        return [
            'separator' => '-',
            'max_length' => 255,
        ];
    }


    // Provides option definition collection set as an array.

    public static function getOptionDefinitions(): array
    {

        return [
            'separator' => [
                'type' => 'string',
                'default' => '-',
                'description' => "Separator string.",
            ],
            'max_length' => [
                'type' => 'integer',
                'min' => 10,
                'default' => 255,
                'description' => "Maximum string length."
            ],
        ];
    }


    // Gets the separator string.

    public function getSeparator(): string
    {

        return $this->options->separator;
    }


    // Gets the maximum string length number.

    public function getMaxLength(): int
    {

        return $this->options->max_length;
    }
}
