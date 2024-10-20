<?php

declare(strict_types=1);

namespace LWP\Components\DataTypes\Custom\Number;

use LWP\Components\Rules\NumberFormattingRule;
use LWP\Components\DataTypes\Natural\Integer\IntegerDataTypeValueContainer;
use LWP\Components\DataTypes\Natural\String\StringDataTypeParserTrait;

class NumberDataTypeParser implements \Stringable
{
    // Enables basic string parsing methods.
    use StringDataTypeParserTrait;


    public const DEFAULT_FRACTIONAL_PART_SEPARATOR = '.';
    public const EXPONENT_DEFAULT_SYMBOL = 'E';

    private string $integer_part;
    private ?string $fractional_part = null;
    private ?int $exponent = null;
    private bool $exponent_is_negative;
    private NumberPartParser $integer_part_parser;


    public function __construct(
        private string $number,
        private ?NumberFormattingRule $formatting_rule = null,
    ) {

        // Normalize property name for "StringDataTypeParserTrait".
        $this->value = $number;

        // The strategy is to look for an E notation first.
        $parts = self::splitAtENotation($number);

        $working_number = $parts['number'];
        $this->exponent = $parts['exponent'];

        $fractional_part_separator = ($formatting_rule)
            // Preferred option to look for.
            ? $formatting_rule->getFractionalPartSeparator()
            // Since at the moment it does not support groups inside the fractional part, look for the rightmost non-digit.
            : null;

        $parts = self::splitAtFractionalPartSeparator($working_number, $fractional_part_separator);

        // Currently it does not support groups inside the fractional part.
        if ($parts['fractional_part'] && !ctype_digit($parts['fractional_part'])) {
            throw new \Exception(sprintf("Fractional part (%s) must contain digits only.", $parts['fractional_part']));
        }

        $working_number = $this->integer_part = $parts['integer_part'];
        $this->fractional_part = $parts['fractional_part'];

        $preferred_parsing_rules = [];

        if ($formatting_rule) {

            $preferred_parsing_rules = [
                'group_separator' => $formatting_rule->getIntegerPartGroupSeparator(),
                'group_size' => $formatting_rule->getIntegerPartGroupLength(),
                'allow_leading_zeros' => boolval($formatting_rule->getZerofill()),
            ];
        }

        $this->integer_part_parser = new NumberPartParser($working_number, $preferred_parsing_rules);
    }


    //

    public function __toString(): string
    {

        return $this->number;
    }


    //

    public function getIntegerPart(): string
    {

        return $this->integer_part;
    }


    //

    public function getFloat(): float
    {

        $result = $this->integer_part_parser->getInteger();

        if ($this->fractional_part) {
            $result .= (self::DEFAULT_FRACTIONAL_PART_SEPARATOR . $this->fractional_part);
        }

        $float = floatval($result);

        if ($this->exponent) {
            $float = ($float * pow(10, $this->exponent));
        }

        return $float;
    }


    //

    public function getInteger(): int
    {

        return intval($this->getFloat());
    }


    //

    public function getIntegerLength(): int
    {

        $parts = self::parseInteger($this->getInteger());

        return strlen((string)$parts[1]);
    }


    //

    public function getFraction(): string
    {

        $float = $this->getFloat();

        $parts = self::parseFloat($float);

        return ltrim($parts[2], self::DEFAULT_FRACTIONAL_PART_SEPARATOR);
    }


    //

    public function getIntegerDataTypeObject(): IntegerDataTypeValueContainer
    {

        return new IntegerDataTypeValueContainer($this->getInteger());
    }


    //

    public static function splitAtENotation(string $number): array
    {

        $result = [
            'number' => $number,
            'exponent' => null,
        ];

        // Last occurence, case-insensitive.
        if (($pos = strripos($number, self::EXPONENT_DEFAULT_SYMBOL)) !== false) {

            $exponent = $exponent_to_validate = substr($number, ($pos + 1));

            // Strip off the sign symbol if it exists.
            if ($exponent[0] == NumberPartParser::NEGATIVE_SIGN_SYMBOL || $exponent[0] == NumberPartParser::POSITIVE_SIGN_SYMBOL) {
                $exponent_to_validate = substr($exponent, 1);
            }

            // Checking here if exponent is valid.
            if (!ctype_digit($exponent_to_validate)) {
                throw new \LWP\Common\Exceptions\Math\IsNotIntegerException(sprintf("Exponent (%s) should be an integer in number string \"%s\".", $exponent, $number));
            }

            $result = [
                'number' => substr($number, 0, $pos),
                'exponent' => intval($exponent),
            ];
        }

        return $result;
    }


    //

    public static function splitAtFractionalPartSeparator(string $number, ?string $separator = self::DEFAULT_FRACTIONAL_PART_SEPARATOR): array
    {

        $result = [
            'integer_part' => $number,
            'fractional_part' => null,
            'separator' => $separator,
        ];

        // Separator is defined.
        if ($separator !== null) {

            $separator_occurence_count = substr_count($number, $separator);

            if ($separator_occurence_count > 1) {
                throw new \Exception(sprintf(
                    "There can only be one occurence of fractional part separator (%s) in number string (%s). Found %d.",
                    $separator,
                    $number,
                    $separator_occurence_count
                ));
            }

            $pos = strpos($number, $separator);

            // Separator is not defined. Will look for the rightmost non-digit.
        } else {

            $number_length = strlen($number);
            $offset = 1;

            do {

                $char = substr($number, -$offset, 1);

                if (
                    !ctype_digit($char)
                    // Exclude leading dash
                    && ($char !== '-' || $offset !== $number_length)
                ) {
                    $result['separator'] = $char;
                    break;
                }

                $offset++;

            } while ($number_length >= $offset);

            $pos = ($number_length >= $offset)
                ? ($number_length - $offset)
                : false;
        }

        if ($pos !== false) {

            $result['integer_part'] = substr($number, 0, $pos);
            $result['fractional_part'] = substr($number, ($pos + 1));
        }

        return $result;
    }


    //

    public function isSigned(): bool
    {

        return $this->integer_part_parser->isSigned();
    }


    //

    public function isUnsigned(): bool
    {

        return !$this->isSigned();
    }


    //

    public function getFractionalPart(): ?string # Leaving string, because in the future it might supported fractional part groups.
    {
        return $this->fractional_part;
    }


    //

    public function getExponent(): ?int
    {

        return $this->exponent;
    }


    //

    public function getLeadingZerosLength(): int
    {

        return $this->integer_part_parser->getLeadingZerosLength();
    }


    // Parses an integer number and creates a component list.

    public static function parseInteger(int $integer): array
    {

        $is_signed = ($integer < 0);

        if ($is_signed) {
            $integer = abs($integer);
        }

        return [
            ((!$is_signed)
                ? ''
                : NumberPartParser::NEGATIVE_SIGN_SYMBOL),
            $integer,
        ];
    }


    // Parses a float number and creates a component list.
    /* The idea behind the return array structure is to create placeholders for each component in order to be able to join them easily. */

    public static function parseFloat(float $value): array
    {

        // The strategy is to process a string rather than deducting integer part off of the full value, which can result in long fractional part anomalies.
        $value_str = (string)$value;
        $parts = explode(self::DEFAULT_FRACTIONAL_PART_SEPARATOR, $value_str);
        // Normalize integer component.
        $parts[0] = intval($parts[0]);

        // Make room for the fractional part component.
        if (count($parts) == 1) {
            $parts[] = '';
        } else {
            $parts[1] = (self::DEFAULT_FRACTIONAL_PART_SEPARATOR . $parts[1]);
        }

        array_splice($parts, 0, 1, self::parseInteger($parts[0]));

        return $parts;
    }
}
