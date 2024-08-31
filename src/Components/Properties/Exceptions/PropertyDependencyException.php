<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Exceptions;

use LWP\Components\Violations\Violation;

class PropertyDependencyException extends \Exception
{
    public function __construct(
        string $message,
        public readonly ?Violation $violation = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {

        parent::__construct($message, $code, $previous);
    }
}
