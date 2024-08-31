<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class CalcFormattingRule extends FormattingRule
{
    public function __construct(
        array $options = []
    ) {

        parent::__construct($options);
    }


    // Gets default value for each option.

    public static function getOptionDefaultValues(): array
    {

        return [];
    }


    // Gets supported option list.

    public static function getSupportedOptions(): array
    {

        return [
            'subject'
        ];
    }


    // Gets option definitions.

    public static function getOptionDefinitions(): array
    {

        return [
            'subject' => [
                'type' => 'string',
                'in_set' => [
                    'age'
                ],
                'description' => ""
            ]
        ];
    }


    // Returns the subject

    public function getSubject(): string
    {

        return $this->options->subject;
    }
}
