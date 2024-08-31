<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class MinSizeViolation extends Violation
{
    public function __construct(
        private int|float $min_size,
        int|float $value,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value is in fact smaller than the constraint value. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($min_size, $value);
    }


    // Gets the offset number, which is a difference between the minimum size number and the provided number.

    public function getOffset(): int|float
    {

        return ($this->min_size - $this->value);
    }


    // Gets error message string format (usually to be used with "sprintf").

    public function getErrorMessageFormat(): string
    {

        return "Size (%g) of the provided value must be equal to or higher than \"%g\".";
    }


    // Gets extended error message string format (usually to be used with "sprintf").

    public function getExtendedErrorMessageFormat(): string
    {

        return ($this->getErrorMessageFormat()
            . " For instance, %1\$g - %2\$g = %g when it should result in an unsigned number.");
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->value, $this->min_size));
    }


    // Gets the extended error message string.

    public function getExtendedErrorMessageString(): string
    {

        return ($this->extended_error_message_str
            ?: sprintf($this->getExtendedErrorMessageFormat(), $this->value, $this->min_size, ($this->value - $this->min_size)));
    }


    // Supplies custom exception class path.

    public function throwException(?string $exception_class_name = ('LWP\Common\Exceptions\Math\NumberTooSmallException')): void
    {

        parent::throwException($exception_class_name);
    }


    // Provides possible corrections - the minimal accepted number and a couple larger numbers.

    public function getCorrectionOpportunities(): ?array
    {

        return [
            $this->min_size,
            ($this->min_size + 1),
            ($this->min_size + 2),
        ];
    }
}
