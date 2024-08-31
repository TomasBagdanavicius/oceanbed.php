<?php

declare(strict_types=1);

namespace LWP\Database;

use LWP\Common\Array\ArrayCollection;

class QueryCollection extends ArrayCollection implements \Stringable
{
    public function __construct(
        array $data = [],
    ) {

        parent::__construct($data);
    }


    // Join all query string using a semicolon.

    public function __toString(): string
    {

        return implode(';', $this->data);
    }
}
