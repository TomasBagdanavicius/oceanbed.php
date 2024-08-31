<?php

declare(strict_types=1);

namespace LWP\Network\Message;

class PlainTextMessageBody extends MessageBody implements \Stringable
{
    public function __construct(
        private string $body,
    ) {


    }


    //

    public function __toString(): string
    {

        return $this->body;
    }


    //

    public function getSize(): int
    {

        return strlen($this->body);
    }


    // Gets "content-type" header field value.

    public static function getContentTypeHeaderFieldValue(): string
    {

        return 'text/plain';
    }
}
