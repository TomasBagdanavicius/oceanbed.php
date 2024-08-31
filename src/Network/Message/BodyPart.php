<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Network\Headers;

class BodyPart extends Entity
{
    public function __construct(
        MessageBody|Message|Boundary $body,
        ?Headers $headers = null,
    ) {

        parent::__construct($body, $headers);
    }


    // Gets size.

    public function getSize(): int
    {

        // +2 is for a CRLF
        return ($this->headers->getSize() + 2 + $this->body->getSize());
    }
}
