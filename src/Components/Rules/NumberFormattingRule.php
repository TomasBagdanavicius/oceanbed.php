<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Components\DataTypes\Custom\Number\NumberPartParser;

class NumberFormattingRule extends FormattingRule
{
    public function __construct(
        array $options = []
    ) {

        parent::__construct($options);

        # Can potentially be moved to definition rules.
        if ($this->options->fractional_part_separator === self::translateIntegerGroupPartSeparator($this->options->integer_part_group_separator)) {
            throw new \Exception(sprintf("Integer part separator (%s) cannot be the same as fractional part separator.", $this->options->fractional_part_separator));
        }
    }


    //

    public static function getSupportedOptions(): array
    {

        return [
            'fractional_part_length',
            'fractional_part_separator',
            'fractional_part_ignore_zeros',
            'integer_part_group_separator',
            'integer_part_group_length',
            'integer_part_trailing_group_extended',
            'zerofill',
        ];
    }


    //

    public static function getOptionDefaultValues(): array
    {

        return [
            'fractional_part_length' => 2,
            'fractional_part_separator' => '.',
            'fractional_part_ignore_zeros' => false,
            'integer_part_group_separator' => ',',
            'integer_part_group_length' => 3,
            'integer_part_trailing_group_extended' => false,
            'zerofill' => null,
        ];
    }


    // Gets option definitions.

    public static function getOptionDefinitions(): array
    {

        return [
            'fractional_part_length' => [
                'type' => 'integer',
                'min' => 0,
                'max' => 20,
                'default' => 2,
            ],
            'fractional_part_separator' => [
                'type' => 'string',
                'max' => 2,
                'default' => '.',
            ],
            'fractional_part_ignore_zeros' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'integer_part_group_separator' => [
                'type' => 'string',
                'max' => 2,
                'default' => ',',
                'nullable' => true,
            ],
            'integer_part_group_length' => [
                'type' => 'integer',
                'default' => 3,
                'nullable' => true,
            ],
            'integer_part_trailing_group_extended' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'zerofill' => [
                'type' => 'integer',
                'default' => null,
                'nullable' => true,
            ],
        ];
    }


    //

    public function getFractionalPartLength(): int
    {

        return $this->options->fractional_part_length;
    }


    //

    public function getFractionalPartSeparator(): ?string
    {

        return $this->options->fractional_part_separator;
    }


    //

    public function getFractionalPartIgnoreZeros(): bool
    {

        return $this->options->fractional_part_ignore_zeros;
    }


    //

    public function getIntegerPartGroupSeparator(): ?string
    {

        return $this->options->integer_part_group_separator;
    }


    //

    public function getIntegerPartGroupLength(): ?int
    {

        return $this->options->integer_part_group_length;
    }


    //

    public function isIntegerPartTrailingGroupExtended(): bool
    {

        return $this->options->integer_part_trailing_group_extended;
    }


    //

    public function getZerofill(): ?int
    {

        return $this->options->zerofill;
    }


    //

    public static function translateIntegerGroupPartSeparator(mixed $group_separator): string
    {

        return match ($group_separator) {
            // None
            '', null, false => '',
            // Default
            true => NumberPartParser::DEFAULT_INTEGER_PART_GROUP_SEPARATOR,
            // Chosen
            default => $group_separator,
        };
    }
}
