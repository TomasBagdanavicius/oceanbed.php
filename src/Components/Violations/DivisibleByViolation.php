<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class DivisibleByViolation extends Violation
{
    protected int|float $ratio;


    public function __construct(
        private int|float $divisible_by,
        int|float $value,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value does not in fact divide by the constraint value. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($divisible_by, $value);

        $this->ratio = ($value / $divisible_by);
    }


    // Gets the ratio number uncovering how many times the provided value contains the constraint value.

    public function getRatio(): int|float
    {

        return $this->ratio;
    }


    // Gets error message string format (usually to be used with "sprintf").

    public function getErrorMessageFormat(): string
    {

        return "Provided number (%g) does not divide by \"%g\".";
    }


    // Gets extended error message string format (usually to be used with "sprintf").

    public function getExtendedErrorMessageFormat(): string
    {

        return ($this->getErrorMessageFormat() . " For instance, %1\$g %% %2\$g = %g when it should be equal to 0.");
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->value, $this->divisible_by));
    }


    // Gets the extended error message string.

    public function getExtendedErrorMessageString(): string
    {

        return ($this->extended_error_message_str
            ?: sprintf($this->getExtendedErrorMessageFormat(), $this->value, $this->divisible_by, ($this->value % $this->divisible_by)));
    }


    // Supplies custom exception class path.

    public function throwException(?string $exception_class_name = ('LWP\Common\Exceptions\Math\NumberNotDivisibleByException')): void
    {

        parent::throwException($exception_class_name);
    }


    // Calculates two closest numbers that are divisible by the required number. One number is smaller than the provided number, whereas the other one is bigger.

    public function getCorrectionOpportunities(): ?array
    {

        $ratio_floored = floor($this->ratio);
        $ratio_fraction = ($this->ratio - $ratio_floored);
        $ratio_ceiled = ceil($this->ratio);

        $smaller = ($this->divisible_by * $ratio_floored);
        $bigger = ($this->divisible_by * $ratio_ceiled);

        $result[] = $smaller;

        // Make sure the closest number is the first element in the resulting array.
        if ($ratio_fraction < 0.5) {
            $result[] = $bigger;
        } else {
            array_unshift($result, $bigger);
        }

        return $result;
    }
}
