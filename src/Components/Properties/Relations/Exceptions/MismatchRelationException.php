<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Relations\Exceptions;

use LWP\Components\Properties\Exceptions\PropertyDependencyException;
use LWP\Components\Violations\Violation;

class MismatchRelationException extends PropertyDependencyException
{
    public function __construct(
        string $message,
        ?Violation $violation = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {

        parent::__construct($message, $violation, $code, $previous);
    }
}
