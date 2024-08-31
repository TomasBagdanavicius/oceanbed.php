<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Components\Rules\NumberFormattingRule;

class NumberDataTypeBuilder
{
    public function __construct(
        /* The approach is to allow modifying main properties publicly. Builder recalculates all properties with each build. */
        public NumberFormattingRule $formatting_rule,
        public int $integer_part,
        public null|string|int $fractional_part = null,
        public ?int $exponent = null,
    ) {

    }


    //

    public function getFractionalPartSeparator(): string
    {

        return ($this->formatting_rule->getFractionalPartSeparator() ?: NumberDataTypeParser::DEFAULT_FRACTIONAL_PART_SEPARATOR);
    }


    //

    public function getIntegerGroupPartSeparator(): string
    {

        return $this->formatting_rule::translateIntegerGroupPartSeparator($this->formatting_rule->getIntegerPartGroupSeparator());
    }


    //

    public function getIntegerPartGroupLength(): ?int
    {

        return ($this->formatting_rule->getIntegerPartGroupLength() ?: 3);
    }


    //

    public static function splitIntegerIntoGroups(int $integer, int $group_length, ?int $zerofill = null, bool $trailing_group_extended = false): array
    {

        $integer_parts = NumberDataTypeParser::parseInteger($integer);

        // Stringify integer digits.
        $integer = (string)$integer_parts[1];

        if ($zerofill) {
            $integer = str_pad($integer, $zerofill, '0', STR_PAD_LEFT);
        }

        $integer_length = strlen($integer);
        $groups = [];
        $offset = 0;

        while ($offset < $integer_length) {

            // Trailing extension bias.
            $bias = intval($trailing_group_extended);

            $remaining = min(($integer_length - $offset), ($group_length + $bias));
            $offset += ($group_length + $bias);

            $part = substr($integer, -min($integer_length, $offset), $remaining);
            array_unshift($groups, $part);
        }

        // Signed.
        if ($integer_parts[0]) {
            array_unshift($groups, $integer_parts[0]);
        }

        return $groups;
    }


    //

    public function build(): string
    {

        // No support for non-digit characters for now.
        if ($this->fractional_part && !ctype_digit((string)$this->fractional_part)) {
            throw new \Exception(sprintf("Fractional part (%d) should contain digits only.", $this->fractional_part));
        }

        $groups = self::splitIntegerIntoGroups(
            $this->integer_part,
            $this->getIntegerPartGroupLength(),
            $this->formatting_rule->getZerofill(),
            $this->formatting_rule->isIntegerPartTrailingGroupExtended(),
        );

        $result = '';

        // The first group member is a minus sign.
        if ($groups[0] == NumberPartParser::NEGATIVE_SIGN_SYMBOL) {
            $result .= array_shift($groups);
        }

        $result .= implode($this->getIntegerGroupPartSeparator(), $groups);

        if ($this->fractional_part) {
            $result .= ($this->getFractionalPartSeparator() . $this->fractional_part);
        }

        if ($this->exponent) {
            $result .= (NumberDataTypeParser::EXPONENT_DEFAULT_SYMBOL . $this->exponent);
        }

        return $result;
    }
}
