<?php

declare(strict_types=1);

namespace LWP\Network\Message;

use LWP\Network\Headers;

abstract class MessageBody implements \Stringable
{
    abstract public function __toString(): string;


    abstract public static function getContentTypeHeaderFieldValue(): string;


    abstract public function getSize(): int;


    // Populates "content-type" header field value through the headers object class.

    public function yieldContentTypeHeader(Headers &$headers): void
    {

        $headers->set('content-type', static::getContentTypeHeaderFieldValue());
    }
}
