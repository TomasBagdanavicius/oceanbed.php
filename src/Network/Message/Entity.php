<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Network\Headers;

class Entity implements \Stringable
{
    public function __construct(
        protected MessageBody|Message|Boundary $body,
        protected ?Headers $headers = null,
    ) {

        if (!$headers) {
            $this->headers = new Headers();
        }
    }


    // Gets the message entity as a string.

    public function __toString(): string
    {

        // Headers string adds one CRLF line break at the end.
        return ($this->headers->__toString() . "\r\n" . $this->body->__toString());
    }


    // Gets size of the entire entity.

    public function getSize(): int
    {

        $result = $this->headers->getSize();
        // Blank line.
        $result += 2;
        $result += $this->getContentSize();

        return $result;
    }


    // Gets the content size.

    public function getContentSize(): int
    {

        return $this->body->getSize();
    }


    // Adds the "content-length" header field with the calculated size value.

    public function addContentLengthHeaderField(): void
    {

        $this->headers->setAndReplace('content-length', (string)$this->getContentSize());
    }


    // Gets the body object.

    private function getBody(): MessageBody|Message|Boundary
    {

        return $this->body;
    }


    // Gets the headers object.

    private function getHeaders(): Headers
    {

        return $this->headers;
    }
}
