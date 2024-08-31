<?php

declare(strict_types=1);

namespace LWP\Network\Http\Message;

use LWP\Network\Message\MultiPartMessage as NetworkMultiPartMessage;

class MultiPartMessage extends NetworkMultiPartMessage
{
    public function __construct(
        Headers $headers,
        Boundary $boundary,
        string $preamble = null,
        string $epilogue = null,
    ) {

        parent::__construct($headers, $boundary, NetworkMultiPartMessage::SUBTYPE_FORM_DATA, $preamble, $epilogue);
    }
}
