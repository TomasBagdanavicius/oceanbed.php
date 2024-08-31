<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class JsonFormattingRule extends FormattingRule
{
    public function __construct(
        array $options = []
    ) {

        parent::__construct($options);
    }


    // Gets default value for each option

    public static function getOptionDefaultValues(): array
    {

        return [
            'depth' => null,
            'force_object' => false
        ];
    }


    // Gets supported option list.

    public static function getSupportedOptions(): array
    {

        return [
            'depth',
            'force_object'
        ];
    }


    // Gets option definitions

    public static function getOptionDefinitions(): array
    {

        return [
            'depth' => [
                'type' => 'integer',
                'min' => 1,
                'description' => ""
            ],
            'force_object' => [
                'type' => 'boolean',
                'default' => false,
                'description' => ""
            ]
        ];
    }


    // Returns the depth

    public function getDepth(): ?int
    {

        return $this->options->depth;
    }


    // Returns the force object option value

    public function getForceObject(): bool
    {

        return $this->options->force_object;
    }
}
