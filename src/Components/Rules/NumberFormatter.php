<?php

declare(strict_types=1);

namespace LWP\Components\Rules;

use LWP\Components\DataTypes\Custom\Number\NumberDataTypeParser;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeBuilder;
use LWP\Components\DataTypes\Custom\Number\NumberDataTypeValueContainer;
use LWP\Common\String\Str;

class NumberFormatter implements FormatterInterface
{
    public function __construct(
        public readonly NumberFormattingRule $formatting_rule
    ) {

    }


    // Formats given number according to number formatting rule requirements.

    public function format(int|float|string|NumberDataTypeValueContainer $number): string
    {

        $fractional_part_ignore_zeros = $this->formatting_rule->getFractionalPartIgnoreZeros();
        $fractional_part_length = $this->formatting_rule->getFractionalPartLength();

        if (is_int($number)) {

            $integer = $number;
            $fractional_part = (!$fractional_part_ignore_zeros)
                ? self::formatBasicFractionalPart('', $fractional_part_length)
                : '';

        } elseif (is_float($number)) {

            $parts = NumberDataTypeParser::parseFloat($number);

            // Sign-less integer.
            $integer = $parts[1];
            $fractional_part = self::formatBasicFractionalPart(
                // Function "parseFloat" gives the fractional part with a prefix.
                $fractional_part = ltrim($parts[2], NumberDataTypeParser::DEFAULT_FRACTIONAL_PART_SEPARATOR),
                $fractional_part_length,
                $fractional_part_ignore_zeros
            );

        } else {

            if (is_string($number)) {

                // Free-style parsing.
                $number_parser = new NumberDataTypeParser($number);

                // Calculated integer and calculated fraction is required (in case scientific notation is used).
                $fraction = $number_parser->getFraction();
                $integer = $number_parser->getInteger();
                $fractional_part = self::formatBasicFractionalPart($fraction, $fractional_part_length, $fractional_part_ignore_zeros);

            } elseif ($number instanceof NumberDataTypeValueContainer) {

                $number_parser = $number->getParser();
                $fraction = $number_parser->getFraction();
                $integer = $number_parser->getInteger();
                $fractional_part = self::formatBasicFractionalPart($fraction, $fractional_part_length, $fractional_part_ignore_zeros);
            }
        }

        $number_builder = new NumberDataTypeBuilder($this->formatting_rule, $integer, $fractional_part);

        return $number_builder->build();
    }


    // Formats fractional part to length constrain requirements.
    // Does not support complex grouping.

    public static function formatBasicFractionalPart(string $fractional_part = '', int $length = 2, bool $ignore_zeros = false): string
    {

        if (!$length) {

            return '';

        } elseif ($fractional_part === '') {

            return (!$ignore_zeros)
                ? str_repeat('0', $length)
                : '';

        } else {

            $fractional_part_length = strlen($fractional_part);
            $result = null;

            if ($fractional_part_length < $length) {
                // Append additional zero digits.
                $result = str_pad($fractional_part, $length, '0');
            } elseif ($fractional_part_length > $length) {
                // Decrease the length to obey the length constraint.
                $result = substr($fractional_part, 0, $length);
            }

            if ($result !== null) {

                return (!$ignore_zeros || substr($result, 0, 1) !== '0' || !Str::hasSameChars($result))
                    ? $result
                    : '';
            }
        }

        return $fractional_part;
    }


    //

    public function canFormat(mixed $value): bool
    {

        return (is_int($value) || is_float($value) || is_string($value) || $value instanceof NumberDataTypeValueContainer);
    }
}
