<?php

declare(strict_types=1);

namespace LWP\Components\Properties\Exceptions;

use LWP\Common\Exceptions\ElementNotFoundException;

class PropertyNotFoundException extends ElementNotFoundException
{
    public function __construct(
        string $message,
        public readonly string $property_name,
        int $code = 0,
        ?\Throwable $previous = null
    ) {

        \Exception::__construct($message, $code, $previous);
    }
}
