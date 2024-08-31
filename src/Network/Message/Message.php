<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Network\Headers;

class Message extends Entity
{
    public function __construct(Headers $headers, MessageBody|Boundary $body)
    {

        parent::__construct($body, $headers);
    }
}
