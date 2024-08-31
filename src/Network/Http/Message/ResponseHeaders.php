<?php

declare(strict_types=1);

namespace LWP\Network\Http\Message;

use LWP\Network\Headers;
use LWP\Network\Http\Message\StatusLine;
use LWP\Network\Uri\UriReference;

class ResponseHeaders extends Headers implements \Stringable
{
    private StatusLine $status_line;


    public function __construct(
        StatusLine $status_line,
        array $data = []
    ) {

        $this->startCollecting();

        parent::__construct($data);

        $this->status_line = $status_line;
    }


    // Outputs response headers to a string.

    public function __toString(): string
    {

        return ($this->status_line->__toString() . "\r\n" . parent::__toString());
    }


    // Gets the status line instance object.

    public function getStatusLine(): StatusLine
    {

        return $this->status_line;
    }


    // Tells if location header field was set.

    public function hasNextLocation(): bool
    {

        return $this->containsKey('location');
    }


    // Gets next location URI reference, if available.

    public function getNextLocation(): ?UriReference
    {

        return ($this->hasNextLocation())
            /* UriReference instead of UrlReference, because in UrlReference relative path names,
            that are not preceeded by a forward slash, will be considered as authority, eg. "file.txt"
            will be considered authority. */
            ? new UriReference($this->get('location'))
            : null;
    }
}
