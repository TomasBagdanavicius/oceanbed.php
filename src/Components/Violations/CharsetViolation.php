<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class CharsetViolation extends Violation
{
    public function __construct(
        private string $charset,
        string $value,
        private array $accepted_charsets
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value is in fact satisfies the constraint value. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($charset, $value);
    }


    // Gets error message text template.

    public function getErrorMessageFormat(array $params = []): string
    {

        return "Given value is not of \"%s\" character set.";
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->charset));
    }
}
