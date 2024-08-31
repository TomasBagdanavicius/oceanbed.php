<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class SizeRangeViolation extends Violation
{
    public function __construct(
        private int|float $range_min_value,
        private int|float $range_max_value,
        int|float|string $value,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value is in fact {{EXPLAIN}} the constraint value. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct(
            ['min' => $range_min_value, 'max' => $range_max_value],
            $value,
        );
    }


    // Gets error message text template.

    public function getErrorMessageFormat(array $params = []): string
    {

        return "Size (%g) of the provided value must be within the range of %s.";
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->value, ($this->range_min_value . " and " . $this->range_max_value)));
    }


    // Supplies custom exception class path.

    public function throwException(?string $exception_class_name = ('\RangeException')): void
    {

        parent::throwException($exception_class_name);
    }


    // Provides possible corrections - the smallest and the biggest accepted numbers.

    public function getCorrectionOpportunities(): ?array
    {

        return [
            $this->range_min_value,
            $this->range_max_value,
        ];
    }


    // Tells if the given value is below the minimum part of the range.

    public function isUnderflow(): bool
    {

        return ($this->value < $this->range_min_value);
    }


    // Tells if the given value is above the maximum part of the range.

    public function isOverflow(): bool
    {

        return ($this->value > $this->range_max_value);
    }


    // Gets offset difference between the given number and one of the range numbers depending on whether it is underflowing or overflowing.

    public function getOffset(): int|float
    {

        // Negative number when underflow, and positive, when overflow.
        return ($this->isUnderflow())
            ? ($this->value - $this->range_min_value)
            : ($this->value - $this->range_max_value);
    }
}
