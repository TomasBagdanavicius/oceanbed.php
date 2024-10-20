<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class ConcatFormattingRule extends FormattingRule
{
    public function __construct(
        array $options = []
    ) {

        parent::__construct($options);
    }


    // Gets default value for each option.

    public static function getOptionDefaultValues(): array
    {

        return [
            'separator' => ' ',
            'shrink' => false,
        ];
    }


    // Gets supported option list.

    public static function getSupportedOptions(): array
    {

        return [
            'separator',
            'shrink',
            'shrink_order',
        ];
    }


    // Gets option definitions.

    public static function getOptionDefinitions(): array
    {

        return [
            'separator' => [
                'type' => 'string',
                'default' => ' ',
                'allow_empty' => true,
                'description' => "Separator",
            ],
            'shrink' => [
                'type' => 'boolean',
                'default' => false,
                'description' => "Whether it is allowed to omit trailing parts of the concatenated result",
            ],
            'shrink_order' => [
                'type' => 'array',
                'default' => false,
                'description' => "Defines what order of elements should be used when concatenated result is being shrunk",
            ],
        ];
    }


    // Returns the separator

    public function getSeparator(): string
    {

        return $this->options->separator;
    }


    // Returns the shrink option

    public function getShrink(): bool
    {

        return $this->options->shrink;
    }


    // Returns the shrink order

    public function getShrinkOrder(): bool
    {

        return $this->options->shrink_order;
    }
}
