<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class MaxSizeViolation extends Violation
{
    public function __construct(
        private int|float $max_size,
        int|float|string $value,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value is in fact smaller than the constraint value. This issues is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($max_size, $value);
    }


    // Gets the offset number, which is a difference between the minimum size number and the provided number.

    public function getOffset(): int|float
    {

        return ($this->value - $this->max_size);
    }


    // Gets error message string format (usually to be used with "sprintf").

    public function getErrorMessageFormat(): string
    {

        return "Size (%g) of the provided value must not exceed the maximum allowed limit of \"%g\".";
    }


    // Gets extended error message string format (usually to be used with "sprintf").

    public function getExtendedErrorMessageFormat(): string
    {

        return ($this->getErrorMessageFormat() . " For instance, %2\$g - %1\$g = %g when it should result in an unsigned number.");
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->value, $this->max_size));
    }


    // Gets the extended error message string.

    public function getExtendedErrorMessageString(): string
    {

        return ($this->extended_error_message_str
            ?: sprintf($this->getExtendedErrorMessageFormat(), $this->value, $this->max_size, ($this->max_size - $this->value)));
    }


    // Supplies custom exception class path.

    public function throwException(?string $exception_class_name = ('LWP\Common\Exceptions\Math\NumberTooLargeException')): void
    {

        parent::throwException($exception_class_name);
    }


    // Gets possible corrections - the maximum accepted number and a couple smaller numbers.

    public function getCorrectionOpportunities(): ?array
    {

        return [
            $this->max_size,
            ($this->max_size - 1),
            ($this->max_size - 2),
        ];
    }
}
