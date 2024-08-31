<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class RequiredObligationViolation extends Violation
{
    public function __construct(
        private bool $required_obligation,
        bool $value,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value meets the constraint value. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($required_obligation, $value);
    }


    // Gets error message text template.

    public function getErrorMessageFormat(): string
    {

        return ($this->required_obligation)
            ? "This element is required."
            : "This element is not required.";
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: $this->getErrorMessageFormat());
    }


    // Gets the extended error message string.

    public function getExtendedErrorMessageString(): string
    {

        $message_format = self::getErrorMessageString();

        return ($this->required_obligation)
            ? ($message_format . " Please make sure it is provided.")
            : $message_format;
    }


    // Supplies custom exception class path.

    public function throwException(?string $exception_class_name = null): void
    {

        $exception_class_name = ($this->required_obligation)
            ? 'LWP\Common\Exceptions\NotFoundException'
            : 'LWP\Common\Exceptions\NotRequiredException';

        parent::throwException($exception_class_name);
    }
}
