<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

class StringTrimFormattingRule extends FormattingRule
{
    public const SIDE_BOTH = 'both';
    public const SIDE_LEADING = 'leading';
    public const SIDE_TRAILING = 'trailing';
    public const MASK_AS_CHARS = 'chars';
    public const MASK_AS_SUBSTRING = 'substring';


    public function __construct(
        array $options = [],
    ) {

        parent::__construct($options);
    }


    // Gets supported options list.

    public static function getSupportedOptions(): array
    {

        return [
            'side',
            'mask',
            'mask_as',
            'repeatable',
        ];
    }


    // Gets default value for each option.

    public static function getOptionDefaultValues(): array
    {

        return [
            'side' => self::SIDE_BOTH,
            'mask' => chr(32), // A single whitespace character.
            'mask_as' => self::MASK_AS_CHARS,
            'repeatable' => true,
        ];
    }


    // Gets option definitions.

    public static function getOptionDefinitions(): array
    {

        return [
            'side' => [
                'type' => 'string',
                'in_set' => [
                    self::SIDE_LEADING,
                    self::SIDE_TRAILING,
                    self::SIDE_BOTH,
                ],
                'default' => self::SIDE_BOTH,
                'description' => "Defines which side of string should be trimmed off.",
            ],
            'mask' => [
                'type' => 'string',
                'allow_empty' => true,
                'default' => chr(32),
                'description' => "String or a character set that should be removed.",
            ],
            'mask_as' => [
                'type' => 'string',
                'in_set' => [
                    self::MASK_AS_CHARS,
                    self::MASK_AS_SUBSTRING,
                ],
                'description' => "Defines whether \"mask\" option should be considered as a substring or a group or characters.",
            ],
            'repeatable' => [
                'type' => 'boolean',
                'default' => true,
                'description' => "Defines whether multiple occurences of that substring or character set chars should be trimmed off.",
            ],
        ];
    }


    // Gets the trim side.

    public function getSide(): bool|string
    {

        return $this->options->side;
    }


    // Gets the string to be trimmed.

    public function getMask(): string
    {

        return $this->options->mask;
    }


    // Tells if remove string should be considered as a word or a group of characters.

    public function getMaskAs(): string
    {

        return $this->options->mask_as;
    }


    // Tells if remove string should be repeatable.

    public function getRepeatable(): bool
    {

        return $this->options->repeatable;
    }


    // Gives PHP's default character group.

    public static function getPHPDefaultCharacterGroup(): string
    {

        return (chr(32) . "\n\r\t\v\x00");
    }
}
