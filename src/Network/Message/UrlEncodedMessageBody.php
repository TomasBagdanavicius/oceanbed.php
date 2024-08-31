<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Network\Uri\SearchParams;

class UrlEncodedMessageBody extends MessageBody implements \Stringable
{
    private array $data = [];


    public function __construct(array $data)
    {

        $this->data = $data;
    }


    // Outputs message body into a string.

    public function __toString(): string
    {

        $search_params = new SearchParams($this->data);

        return $search_params->__toString();
    }


    // Gets the size.

    public function getSize(): int
    {

        return strlen($this->__toString());
    }


    // Gets 'content-type' header field value.

    public static function getContentTypeHeaderFieldValue(): string
    {

        return 'application/x-www-form-urlencoded';
    }
}
