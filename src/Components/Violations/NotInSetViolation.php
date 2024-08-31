<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class NotInSetViolation extends Violation
{
    public function __construct(
        private array $set,
        string|array $value,
        protected string|array $intersecting_values,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value does in fact intersect with the set. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($set, $value);
    }


    // Gets the intersecting value(s).

    public function getIntersectingValues(): string|array
    {

        return $this->intersecting_values;
    }


    // Gets error message string format (usually to be used with "sprintf").

    public function getErrorMessageFormat(): string
    {

        return "Some elements were found in the set: %s.";
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), InSetViolation::getArrayValuesAsQuotedStrings($this->intersecting_values)));
    }


    // Provides possible corrections.

    public function getCorrectionOpportunities(): ?array
    {

        return null;
    }
}
