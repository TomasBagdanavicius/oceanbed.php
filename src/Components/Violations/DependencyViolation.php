<?php

declare(strict_types=1);

namespace LWP\Components\Violations;

class DependencyViolation extends Violation
{
    public function __construct(
        private string|array $dependencies,
        string $value,
    ) {

        /* Warning! There is no explicit validation here to verify that the provided value is in fact satisfies the constraint value. This issue is subject to the outside functionality, which must make sure that there is no hoax when calling this class. */

        parent::__construct($dependencies, $value);
    }


    // Gets error message text template.

    public function getErrorMessageFormat(array $params = []): string
    {

        return "Element \"%s\" is dependent upon %s.";
    }


    // Gets the regular error message string.

    public function getErrorMessageString(): string
    {

        return ($this->error_message_str
            ?: sprintf($this->getErrorMessageFormat(), $this->value, '"' . implode('", "', (array)$this->dependencies) . '"'));
    }
}
