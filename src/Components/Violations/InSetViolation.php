<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class InSetViolation extends Violation
{
    public function __construct(
        private array $set,
        string|int|array $value,
        public readonly string|int|array $missing_values
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value does not in fact intersect with the set. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($set, $value);
    }


    // Gets error message string format (usually to be used with "sprintf").

    public function getErrorMessageFormat(): string
    {

        return "Some elements were not found in the set: %s.";
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), self::getArrayValuesAsQuotedStrings($this->missing_values)));
    }


    // Provides possible corrections.

    public function getCorrectionOpportunities(): ?array
    {

        return (array)$this->set;
    }


    // Formats the missing values into a string, having all element individually quoted.

    public static function getArrayValuesAsQuotedStrings(string|int|array $array, ?int $max = 5): string
    {

        if (is_array($array)) {

            $values = ($max)
                ? array_slice($array, 0, $max)
                : $array;
            $result = ('"' . implode('", "', $values) . '"');

            if ($max && $max < count($array)) {
                $result .= ', etc';
            }

            return $result;

        } else {

            return ('"' . $array . '"');
        }
    }
}
