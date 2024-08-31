<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Exceptions;

class PropertyTypeError extends \TypeError
{
    public function __construct(
        string $message,
        public readonly string $property_name,
        int $code = 0,
        ?\Throwable $previous = null
    ) {

        parent::__construct($message, $code, $previous);
    }
}
