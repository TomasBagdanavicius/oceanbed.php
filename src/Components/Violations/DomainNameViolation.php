<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class DomainNameViolation extends Violation
{
    public function __construct(
        private string $domain_name,
        string $value,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value in fact obeys the constraint value. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($domain_name, $value);
    }


    // Gets error message string format.

    public function getErrorMessageFormat(): string
    {

        return "Domain name \"%s\" is not allowed.";
    }
}
