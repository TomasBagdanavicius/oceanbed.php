<?php

declare(strict_types=1);

namespace LWP\Network\Http\Message;

use LWP\Network\Headers;
use LWP\Network\Http\Message\StartLine;

class RequestHeaders extends Headers implements \Stringable
{
    public function __construct(
        public readonly StartLine $start_line,
        array $data = []
    ) {

        $this->startCollecting();

        parent::__construct($data);
    }


    // Outputs request headers as a string.

    public function __toString(): string
    {

        // "Host" header field should be put first.
        uksort($this->data, function (string $a, string $b): int {

            return (strcmp($a, 'host') === 0)
                ? -1
                : 1;
        });

        return ($this->start_line->__toString() . "\r\n" . parent::__toString());
    }
}
